<?php

namespace Database\Seeders;

use App\Models\Conference;
use App\Models\JournalPartner;
use Illuminate\Database\Seeder;

class JournalPartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conference = Conference::where('is_active', true)->first() ?? Conference::first();

        if (!$conference) {
            return;
        }

        $journals = [
            [
                'conference_id'           => $conference->id,
                'journal_name'            => 'Jurnal Kesehatan Vokasional (JKesVo)',
                'university'              => 'Universitas Gadjah Mada',
                'website'                 => 'https://journal.ugm.ac.id/jkesvo',
                'loa_information'         => 'Ya, jurnal dapat menerbitkan LoA dan/atau Conditional LoA sebelum publikasi',
                'submission_requirements' => "Manuscripts submitted from ICHA 2026 should be original research articles and must be within the scope of Jurnal Kesehatan Vokasional (JKesVo).\n\nManuscripts should be prepared using the JKesVo article template and submitted in English. The manuscript should comply with JKesVo's author guidelines, including an abstract of no more than 250 words and at least 80% of references from scientific journal articles.\n\nThe similarity score should not exceed 20%. Manuscripts must be original, not previously published, and not under consideration for publication in another journal.",
            ],
            [
                'conference_id'           => $conference->id,
                'journal_name'            => 'Jurnal Ekonomi Kesehatan Indonesia (JEKI)',
                'university'              => 'Universitas Indonesia',
                'website'                 => 'https://scholarhub.ui.ac.id/eki/',
                'loa_information'         => 'Ya, jurnal dapat menerbitkan LoA dan/atau Conditional LoA sebelum publikasi',
                'submission_requirements' => "Jenis Artikel:\nManuscript dapat berupa original research article, case study, conceptual paper, review-based study yang relevan dan terbatas, atau protocol.\n\nKesesuaian Scope:\nManuscript harus sesuai dengan fokus dan scope Jurnal Ekonomi Kesehatan Indonesia, khususnya bidang health economics dan health policy dengan pendekatan ekonomi (economic evaluation, health financing, health policy, health insurance, pharmacoeconomics, HTA, JKN, dll).\n\nFormat & Bahasa:\nDisiapkan menggunakan template dan Author Guidelines JEKI. Artikel dapat ditulis dalam Bahasa Indonesia atau Bahasa Inggris.\n\nOriginalitas & Similarity:\nHasil similarity check harus di bawah 20% (Turnitin / iThenticate). Penulis wajib memastikan seluruh penulis telah menyetujui isi manuscript.",
            ],
            [
                'conference_id'           => $conference->id,
                'journal_name'            => 'Jurnal Untuk Masyarakat Sehat (JUKMAS)',
                'university'              => 'Universitas Respati Indonesia',
                'website'                 => 'https://journal.urindo.ac.id/index.php/jukmas/index',
                'loa_information'         => 'Ya, jurnal dapat menerbitkan LoA dan/atau Conditional LoA sebelum publikasi',
                'submission_requirements' => "Pengusulan Jenis Artikel:\nDiutamakan untuk artikel berjenis Review dan Studi Kasus di bidang kesehatan masyarakat dan administrasi rumah sakit.\n\nKetentuan Teknis:\nFormat naskah dan sitasi mengikuti template resmi JUKMAS dengan batas uji kemiripan (similarity) maksimal sesuai ketentuan jurnal.",
            ],
            [
                'conference_id'           => $conference->id,
                'journal_name'            => 'Journal of Indonesian Health Policy and Administration (IHPA)',
                'university'              => 'Universitas Indonesia',
                'website'                 => 'https://scholarhub.ui.ac.id/ihpa/',
                'loa_information'         => 'Ya, jurnal dapat menerbitkan LoA dan/atau Conditional LoA sebelum publikasi',
                'submission_requirements' => "Jenis Artikel:\nUntuk saat ini, penulis lebih disarankan mengajukan original research article / case study, karena kuota untuk artikel berbasis review saat ini sudah penuh hingga tahun 2027.\n\nKebijakan LoA:\nLoA dapat diproses ketika manuskrip sudah accepted pada tahap peer-review dan penulis sudah melakukan revisi minimal 1x.\n\nPedoman Penulisan:\nAuthor guidelines & template dapat diakses melalui website resmi. Maksimal uji similarity Turnitin adalah 20%.",
            ],
        ];

        foreach ($journals as $journal) {
            JournalPartner::updateOrCreate(
                [
                    'conference_id' => $journal['conference_id'],
                    'journal_name'  => $journal['journal_name'],
                ],
                $journal
            );
        }
    }
}
