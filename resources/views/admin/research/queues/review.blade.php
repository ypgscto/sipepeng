<x-app-layout>
    <x-slot name="header">Antrian Review</x-slot>
    <div class="sipeng-page">
        <x-sipeng.page-header title="Antrian Review Proposal" />
        <div class="sipeng-card overflow-hidden">
            <div class="sipeng-card-body p-0">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50"><tr>
                        <th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Judul</th>
                        <th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y">
                        @forelse ($proposals as $proposal)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">{{ $proposal->proposal_number }}</td>
                                <td class="px-4 py-3">{{ Str::limit($proposal->judul, 50) }}</td>
                                <td class="px-4 py-3">@include('admin.research.partials.status-badge', ['proposal' => $proposal])</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('admin.research.show', $proposal) }}" class="text-emerald-700 text-xs hover:underline">Buka</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Antrian kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($proposals->hasPages())<div class="px-4 py-3">{{ $proposals->links() }}</div>@endif
            </div>
        </div>
    </div>
</x-app-layout>
