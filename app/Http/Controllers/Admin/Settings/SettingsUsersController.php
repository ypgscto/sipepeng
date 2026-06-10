<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateApplicationUserRequest;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsUsersController extends Controller
{
    public function index(Request $request): View
    {
        $items = User::query()
            ->with('activeRoles')
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('siakad_login', 'like', $term);
                });
            })
            ->when($request->filled('role'), function ($q) use ($request): void {
                $code = $request->string('role');
                $q->whereHas('activeRoles', fn ($inner) => $inner->where('code', $code));
            })
            ->when($request->string('source') === 'siakad', fn ($q) => $q->siakadSourced())
            ->when($request->string('source') === 'local', fn ($q) => $q->localOnly())
            ->when($request->string('login_access') === 'allowed', fn ($q) => $q->where('is_allowed_login', true))
            ->when($request->string('login_access') === 'blocked', fn ($q) => $q->where('is_allowed_login', false))
            ->orderBy('name')
            ->paginate(config('sipepeng_users.per_page', 20))
            ->withQueryString();

        return view('admin.settings.users.index', [
            'items' => $items,
            'roles' => SipepengRole::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.settings.users.edit', $this->formData($user));
    }

    public function update(UpdateApplicationUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if ($user->isSiakadSourced()) {
            $user->update([
                'is_active' => filter_var($validated['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'is_allowed_login' => filter_var($validated['is_allowed_login'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);
        } else {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            if (! empty($validated['password'])) {
                $user->password = $validated['password'];
            }
            $user->is_active = filter_var($validated['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $user->is_allowed_login = filter_var($validated['is_allowed_login'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $user->save();
        }

        if ($user->hasRole('super_admin')
            && ! in_array('super_admin', $validated['roles'], true)
            && User::query()->whereHas('activeRoles', fn ($q) => $q->where('code', 'super_admin'))
                ->where('id', '!=', $user->id)->doesntExist()) {
            return back()
                ->with('error', 'Minimal harus ada satu super admin.')
                ->withInput();
        }

        $this->syncUserRoles($user, $validated['roles']);

        return redirect()
            ->route('admin.settings.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * @param  list<string>  $roleCodes
     */
    protected function syncUserRoles(User $user, array $roleCodes): void
    {
        $validCodes = array_values(array_intersect(
            $roleCodes,
            config('sipepeng_users.assignable_roles', []),
        ));

        $roles = SipepengRole::query()
            ->active()
            ->whereIn('code', $validCodes)
            ->get()
            ->keyBy('code');

        $primaryCode = $validCodes[0] ?? null;

        foreach ($roles as $role) {
            SipepengUserRoleMapping::query()->updateOrCreate(
                ['user_id' => $user->id, 'role_id' => $role->id],
                [
                    'is_primary' => $role->code === $primaryCode,
                    'is_active' => true,
                    'assigned_at' => now(),
                    'notes' => 'Diatur admin',
                ],
            );
        }

        $activeRoleIds = $roles->pluck('id')->all();
        SipepengUserRoleMapping::query()
            ->where('user_id', $user->id)
            ->whereNotIn('role_id', $activeRoleIds)
            ->update(['is_active' => false]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(User $user): array
    {
        return [
            'user' => $user->load('activeRoles'),
            'roleOptions' => SipepengRole::query()
                ->active()
                ->whereIn('code', config('sipepeng_users.assignable_roles', []))
                ->orderBy('sort_order')
                ->get(),
            'selectedRoles' => $user->roleCodes(),
        ];
    }
}
