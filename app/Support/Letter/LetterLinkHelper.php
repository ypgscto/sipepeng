<?php

namespace App\Support\Letter;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\IntellectualProperty\IpRegistration;
use App\Models\Lppm\LetterType;
use App\Models\Lppm\Partner;
use App\Models\Lppm\Reviewer;
use App\Models\Publication\Publication;
use App\Models\Research\ResearchProposal;

class LetterLinkHelper
{
    /**
     * @return array<string, mixed>
     */
    public static function prefillFromResearch(ResearchProposal $proposal): array
    {
        return [
            'research_proposal_id' => $proposal->id,
            'community_service_proposal_id' => null,
            'proposal_number_snapshot' => $proposal->proposal_number,
            'proposal_judul_snapshot' => $proposal->judul,
            'ketua_dosen_id' => $proposal->ketua_dosen_id,
            'ketua_dosen_nama_snapshot' => $proposal->ketua_dosen_nama_snapshot,
            'prodi_id' => $proposal->prodi_id,
            'prodi_nama_snapshot' => $proposal->prodi_nama_snapshot,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function prefillFromPkm(CommunityServiceProposal $proposal): array
    {
        $data = [
            'community_service_proposal_id' => $proposal->id,
            'research_proposal_id' => null,
            'proposal_number_snapshot' => $proposal->proposal_number,
            'proposal_judul_snapshot' => $proposal->judul,
            'ketua_dosen_id' => $proposal->ketua_dosen_id,
            'ketua_dosen_nama_snapshot' => $proposal->ketua_dosen_nama_snapshot,
            'prodi_id' => $proposal->prodi_id,
            'prodi_nama_snapshot' => $proposal->prodi_nama_snapshot,
        ];

        if ($proposal->mitra_id) {
            $partner = Partner::query()->find($proposal->mitra_id);
            if ($partner) {
                $data['partner_id'] = $partner->id;
                $data['mitra_nama_snapshot'] = $partner->name;
                $data['mitra_alamat_snapshot'] = $partner->address;
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function prefillFromPartner(Partner $partner): array
    {
        return [
            'partner_id' => $partner->id,
            'mitra_nama_snapshot' => $partner->name,
            'mitra_alamat_snapshot' => $partner->address,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function prefillFromReviewer(Reviewer $reviewer): array
    {
        $reviewer->loadMissing('user');

        return [
            'reviewer_id' => $reviewer->id,
            'reviewer_nama_snapshot' => $reviewer->user?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function prefillFromPublication(Publication $publication): array
    {
        return [
            'publication_id' => $publication->id,
            'ip_registration_id' => null,
            'proposal_number_snapshot' => $publication->proposal_number_snapshot,
            'proposal_judul_snapshot' => $publication->proposal_judul_snapshot ?? $publication->judul,
            'prodi_id' => $publication->prodi_id,
            'prodi_nama_snapshot' => $publication->prodi_nama_snapshot,
            'research_proposal_id' => $publication->research_proposal_id,
            'community_service_proposal_id' => $publication->community_service_proposal_id,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function resolveSnapshots(array $data): array
    {
        if (! empty($data['research_proposal_id'])) {
            $proposal = ResearchProposal::query()->find($data['research_proposal_id']);
            if ($proposal) {
                $data = array_merge($data, self::prefillFromResearch($proposal));
            }
        } elseif (! empty($data['community_service_proposal_id'])) {
            $proposal = CommunityServiceProposal::query()->find($data['community_service_proposal_id']);
            if ($proposal) {
                $data = array_merge($data, self::prefillFromPkm($proposal));
            }
        }

        if (! empty($data['partner_id'])) {
            $partner = Partner::query()->find($data['partner_id']);
            if ($partner) {
                $data = array_merge($data, self::prefillFromPartner($partner));
            }
        }

        if (! empty($data['reviewer_id'])) {
            $reviewer = Reviewer::query()->find($data['reviewer_id']);
            if ($reviewer) {
                $data = array_merge($data, self::prefillFromReviewer($reviewer));
            }
        }

        if (! empty($data['publication_id'])) {
            $publication = Publication::query()->find($data['publication_id']);
            if ($publication) {
                $data = array_merge($data, self::prefillFromPublication($publication));
            }
        }

        if (! empty($data['ip_registration_id'])) {
            $ip = IpRegistration::query()->find($data['ip_registration_id']);
            if ($ip) {
                $data['publication_id'] = null;
                $data['proposal_judul_snapshot'] = $data['proposal_judul_snapshot'] ?? $ip->judul;
            }
        }

        return $data;
    }

    public static function defaultPerihal(LetterType $type, ?string $judul = null): string
    {
        $base = $type->name;
        if ($judul) {
            return $base.' — '.mb_substr($judul, 0, 180);
        }

        return $base;
    }

    public static function validateProposalStatus(string $status, ?LetterType $type): bool
    {
        $min = $type?->min_proposal_status ?? 'approved';
        $allowed = config('sipepeng_letters.linkable_proposal_statuses', ['approved']);

        if ($status === $min) {
            return true;
        }

        return in_array($status, $allowed, true);
    }
}
