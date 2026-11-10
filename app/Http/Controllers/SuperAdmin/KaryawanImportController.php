<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use App\Models\MasterData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Import massal karyawan dari CSV / XLSX (Fitur D12).
 *
 * Alur dua langkah agar aman:
 *  1. preview — file diparse & divalidasi per baris, hasilnya ditampilkan
 *     (VALID / error per baris) tanpa menyentuh database.
 *  2. commit  — hanya baris VALID yang disimpan, baris error dilewati dan
 *     dirangkum. Konfirmasi eksplisit diperlukan sebelum commit.
 */
class KaryawanImportController extends Controller
{
    /** Batas baris per file agar tidak membanjiri memori / sesi. */
    private const MAX_ROWS = 1000;

    /** Kolom yang dikenal (header file → kolom database). */
    private const HEADER_MAP = [
        'kode_pegawai' => 'kode_pegawai',
        'kode pegawai' => 'kode_pegawai',
        'nik' => 'nik',
        'nama_lengkap' => 'nama_lengkap',
        'nama lengkap' => 'nama_lengkap',
        'nama' => 'nama_lengkap',
        'email' => 'email',
        'jabatan' => 'jabatan',
        'divisi' => 'divisi',
        'departemen' => 'divisi',
        'golongan' => 'golongan',
        'status' => 'status',
        'tanggal_bergabung' => 'tanggal_bergabung',
        'tanggal bergabung' => 'tanggal_bergabung',
        'tanggal_masuk' => 'tanggal_bergabung',
        'no_telepon' => 'nomor_telepon',
        'nomor_telepon' => 'nomor_telepon',
        'telepon' => 'nomor_telepon',
        'jenis_kelamin' => 'jenis_kelamin',
        'jenis kelamin' => 'jenis_kelamin',
        'alamat' => 'alamat',
        'tanggal_lahir' => 'tanggal_lahir',
        'tempat_lahir' => 'tempat_lahir',
        'npwp' => 'npwp',
    ];

    private const REQUIRED = ['kode_pegawai', 'nama_lengkap', 'email', 'jabatan', 'divisi', 'status', 'tanggal_bergabung'];

    private const STATUS_MAP = [
        'karyawan tetap' => 'Karyawan Tetap',
        'tetap' => 'Karyawan Tetap',
        'permanent' => 'Karyawan Tetap',
        'contract' => 'Contract',
        'kontrak' => 'Contract',
        'internship' => 'Internship',
        'magang' => 'Internship',
        'intern' => 'Internship',
    ];

    public function index()
    {
        return view('superadmin.karyawan-import', [
            'columns' => self::REQUIRED,
            'maxRows' => self::MAX_ROWS,
        ]);
    }

    /** Langkah 1 — parse & tampilkan preview (tanpa menyimpan data). */
    public function preview(Request $request)
    {
        $request->validate([
            'import_file' => ['required', 'file', 'max:5120'],
        ], [
            'import_file.required' => 'Pilih file CSV atau XLSX terlebih dahulu.',
            'import_file.file' => 'File tidak terbaca atau terlalu besar (maks 5 MB).',
        ]);

        $file = $request->file('import_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['csv', 'xlsx'], true)) {
            throw ValidationException::withMessages([
                'import_file' => 'Format file hanya boleh CSV atau XLSX.',
            ]);
        }

        try {
            $rows = $ext === 'csv'
                ? $this->parseCsv($file->getRealPath())
                : $this->parseXlsx($file->getRealPath());
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'import_file' => 'File tidak dapat dibaca. Pastikan format CSV/XLSX valid. (' . $e->getMessage() . ')',
            ]);
        }

        if (count($rows) === 0) {
            throw ValidationException::withMessages([
                'import_file' => 'File tidak berisi data baris (hanya header atau kosong).',
            ]);
        }

        $truncated = false;
        if (count($rows) > self::MAX_ROWS) {
            $rows = array_slice($rows, 0, self::MAX_ROWS);
            $truncated = true;
        }

        $headers = array_keys($rows[0]['raw']);
        $validated = $this->validateRows($rows);

        // Simpan hasil parse di sesi untuk langkah commit.
        session([
            'karyawan_import' => [
                'rows' => $rows,
                'headers' => $headers,
                'created_at' => now()->toDateTimeString(),
                'filename' => $file->getClientOriginalName(),
            ],
        ]);

        $stats = $this->summarize($validated);

        return view('superadmin.karyawan-import', [
            'preview' => $validated,
            'headers' => $headers,
            'stats' => $stats,
            'truncated' => $truncated,
            'filename' => $file->getClientOriginalName(),
            'columns' => self::REQUIRED,
            'maxRows' => self::MAX_ROWS,
        ]);
    }

    /** Langkah 2 — simpan hanya baris yang lolos validasi. */
    public function commit(Request $request)
    {
        $sessionData = session('karyawan_import');

        if (empty($sessionData['rows'])) {
            return redirect()
                ->route('superadmin.karyawan-import.index')
                ->with('error', 'Sesi import habis (halaman terlalu lama terbuka). Unggah file kembali.');
        }

        $validated = $this->validateRows($sessionData['rows'], persistMaster: true);

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($validated as $i => $row) {
            if (!$row['valid']) {
                $skipped++;
                $errors[] = 'Baris ' . $row['row'] . ': ' . implode('; ', $row['errors']);
                continue;
            }

            $data = $row['data'];

            Karyawan::create([
                'kode_pegawai' => $data['kode_pegawai'],
                'nik' => $data['nik'] ?? null,
                'email' => $data['email'],
                'kata_sandi' => Hash::make(Str::random(24)),
                'nama_lengkap' => $data['nama_lengkap'],
                'jabatan' => $data['jabatan'],
                'divisi' => $data['divisi'],
                'golongan' => $data['golongan'] ?? null,
                'status' => $data['status'],
                'tanggal_bergabung' => $data['tanggal_bergabung'],
                'nomor_telepon' => $data['nomor_telepon'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'npwp' => $data['npwp'] ?? null,
                'posisi' => trim($data['divisi']) === 'HRD' ? 'hr' : 'karyawan',
                'nama_bank' => 'BSI',
                'jumlah_anak' => 0,
                'is_resigned' => false,
            ]);

            $created++;
        }

        session()->forget('karyawan_import');

        ActivityLog::record(
            'karyawan.import',
            'Import massal karyawan dari "' . $sessionData['filename'] . '": '
                . $created . ' berhasil, ' . $skipped . ' dilewati.',
            [
                'module' => 'users',
                'metadata' => [
                    'created' => $created,
                    'skipped' => $skipped,
                    'filename' => $sessionData['filename'],
                    'errors' => array_slice($errors, 0, 20),
                ],
            ]
        );

        $message = $created . ' karyawan berhasil ditambahkan.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' baris dilewati (lihat detail di log).';
        }

        return redirect()
            ->route('superadmin.karyawan-import.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    // ================================================================
    // PARSER
    // ================================================================

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('File CSV tidak dapat dibuka.');
        }

        try {
            $first = fgets($handle);
            if ($first === false) {
                return [];
            }

            // Buang BOM UTF-8.
            $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);

            $delimiter = (substr_count($first, ';') > substr_count($first, ','))
                ? ';'
                : ((substr_count($first, "\t") > substr_count($first, ',')) ? "\t" : ',');

            $headerRow = str_getcsv($first, $delimiter);
            $headers = $this->mapHeaders($headerRow);

            $rows = [];
            $line = 1;

            while (($lineData = fgetcsv($handle, 0, $delimiter)) !== false) {
                $line++;

                if (trim(implode('', array_map('strval', $lineData))) === '') {
                    continue;
                }

                $raw = [];
                foreach ($headers as $col => $field) {
                    $raw[$field] = trim((string) ($lineData[$col] ?? ''));
                }

                $rows[] = ['row' => $line, 'raw' => $raw];
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function parseXlsx(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $matrix = $sheet->toArray(null, true, true, false);

        if (count($matrix) < 2) {
            $spreadsheet->disconnectWorksheets();

            return [];
        }

        $headers = $this->mapHeaders(array_map(fn ($v) => (string) $v, array_shift($matrix)));

        $rows = [];
        $line = 1;

        foreach ($matrix as $lineData) {
            $line++;

            $allEmpty = true;
            $raw = [];
            foreach ($headers as $col => $field) {
                $value = $lineData[$col] ?? null;
                $value = $this->stringify($value);
                if ($value !== '') {
                    $allEmpty = false;
                }
                $raw[$field] = $value;
            }

            if ($allEmpty) {
                continue;
            }

            $rows[] = ['row' => $line, 'raw' => $raw];
        }

        $spreadsheet->disconnectWorksheets();

        return $rows;
    }

    /** Excel menyimpan tanggal sebagai float serial / DateTime — samakan ke Y-m-d. */
    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_float($value)) {
            // Serial date Excel (angka 20000–60000 wajar untuk tanggal).
            if ($value > 20000 && $value < 60000 && abs($value - round($value)) < 0.0001) {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', '1899-12-30');
                if ($date !== false) {
                    return $date->modify('+' . (int) round($value) . ' days')->format('Y-m-d');
                }
            }

            return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return trim((string) $value);
    }

    /** Normalisasi header (case, spasi, underscore) ke nama field. */
    private function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $i => $header) {
            $key = mb_strtolower(trim((string) $header));
            $key = str_replace(['-', '/'], ' ', $key);
            $key = preg_replace('/\s+/', ' ', $key);

            $field = self::HEADER_MAP[$key] ?? self::HEADER_MAP[str_replace(' ', '_', $key)] ?? null;

            if ($field !== null) {
                $map[$i] = $field;
            }
        }

        return $map;
    }

    // ================================================================
    // VALIDASI PER BARIS
    // ================================================================

    /**
     * @param array<int, array{row:int, raw:array}> $rows
     * @param bool $persistMaster Sinkronkan data baru ke master_data (hanya saat commit).
     * @return array<int, array{row:int, data:array, valid:bool, errors:array}>
     */
    private function validateRows(array $rows, bool $persistMaster = false): array
    {
        $existingCodes = Karyawan::withTrashed()->pluck('kode_pegawai')->map(fn ($v) => mb_strtolower($v))->all();
        $existingEmails = Karyawan::withTrashed()->pluck('email')->map(fn ($v) => mb_strtolower($v))->all();
        $existingNiks = Karyawan::withTrashed()->pluck('nik')->filter()->map(fn ($v) => mb_strtolower($v))->all();

        $masterDivisi = MasterData::options('divisi');
        $masterJabatan = MasterData::options('jabatan');
        $masterGolongan = MasterData::options('golongan');

        $seenCodes = [];
        $seenEmails = [];

        $result = [];

        foreach ($rows as $row) {
            $raw = $row['raw'];
            $errors = [];
            $data = [];

            foreach (self::REQUIRED as $field) {
                if (trim((string) ($raw[$field] ?? '')) === '') {
                    $errors[] = 'Kolom "' . str_replace('_', ' ', $field) . '" wajib diisi.';
                }
            }

            $data['kode_pegawai'] = trim((string) ($raw['kode_pegawai'] ?? ''));
            $data['nama_lengkap'] = trim((string) ($raw['nama_lengkap'] ?? ''));
            $data['email'] = trim((string) ($raw['email'] ?? ''));
            $data['jabatan'] = trim((string) ($raw['jabatan'] ?? ''));
            $data['divisi'] = trim((string) ($raw['divisi'] ?? ''));
            $data['golongan'] = trim((string) ($raw['golongan'] ?? '')) ?: null;

            if ($data['kode_pegawai'] !== '') {
                $key = mb_strtolower($data['kode_pegawai']);
                if (in_array($key, $existingCodes, true)) {
                    $errors[] = 'Kode pegawai "' . $data['kode_pegawai'] . '" sudah terdaftar.';
                } elseif (isset($seenCodes[$key])) {
                    $errors[] = 'Kode pegawai "' . $data['kode_pegawai'] . '" duplikat di dalam file.';
                }
                $seenCodes[$key] = true;
            }

            if ($data['email'] !== '') {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Email "' . $data['email'] . '" tidak valid.';
                } else {
                    $key = mb_strtolower($data['email']);
                    if (in_array($key, $existingEmails, true)) {
                        $errors[] = 'Email "' . $data['email'] . '" sudah terdaftar.';
                    } elseif (isset($seenEmails[$key])) {
                        $errors[] = 'Email "' . $data['email'] . '" duplikat di dalam file.';
                    }
                    $seenEmails[$key] = true;
                }
            }

            // Status dengan pemetaan alias umum (Kontrak → Contract, dst).
            $statusRaw = trim((string) ($raw['status'] ?? ''));
            if ($statusRaw !== '') {
                $normalized = mb_strtolower($statusRaw);
                $data['status'] = self::STATUS_MAP[$normalized] ?? $statusRaw;

                if (!in_array($data['status'], ['Karyawan Tetap', 'Contract', 'Internship'], true)) {
                    $errors[] = 'Status "' . $statusRaw . '" tidak dikenal (pakai: Karyawan Tetap / Contract / Internship).';
                }
            } else {
                $data['status'] = '';
            }

            // Tanggal bergabung — terima Y-m-d maupun d/m/Y.
            $data['tanggal_bergabung'] = '';
            $tanggal = trim((string) ($raw['tanggal_bergabung'] ?? ''));
            if ($tanggal !== '') {
                $parsed = $this->parseDate($tanggal);
                if ($parsed === null) {
                    $errors[] = 'Tanggal bergabung "' . $tanggal . '" tidak valid (pakai YYYY-MM-DD).';
                } else {
                    $data['tanggal_bergabung'] = $parsed;
                }
            }

            $nik = trim((string) ($raw['nik'] ?? ''));
            if ($nik !== '' && in_array(mb_strtolower($nik), $existingNiks, true)) {
                $errors[] = 'NIK "' . $nik . '" sudah terdaftar.';
            }
            $data['nik'] = $nik ?: null;

            $jenisKelamin = trim((string) ($raw['jenis_kelamin'] ?? ''));
            if ($jenisKelamin !== '') {
                $normalizedJk = mb_strtolower($jenisKelamin);
                $mapped = match (true) {
                    in_array($normalizedJk, ['laki-laki', 'laki laki', 'l', 'male', 'pria'], true) => 'Laki-laki',
                    in_array($normalizedJk, ['perempuan', 'wanita', 'p', 'female'], true) => 'Perempuan',
                    default => null,
                };
                if ($mapped === null) {
                    $errors[] = 'Jenis kelamin "' . $jenisKelamin . '" tidak dikenal (Laki-laki / Perempuan).';
                }
                $data['jenis_kelamin'] = $mapped;
            } else {
                $data['jenis_kelamin'] = null;
            }

            foreach (['nomor_telepon', 'alamat', 'tanggal_lahir', 'tempat_lahir', 'npwp'] as $optional) {
                $data[$optional] = trim((string) ($raw[$optional] ?? '')) ?: null;
            }

            if ($data['tanggal_lahir'] !== null) {
                $parsedLahir = $this->parseDate($data['tanggal_lahir']);
                if ($parsedLahir === null) {
                    $errors[] = 'Tanggal lahir "' . $data['tanggal_lahir'] . '" tidak valid.';
                } else {
                    $data['tanggal_lahir'] = $parsedLahir;
                }
            }

            // Sinkronkan ke master data bila tersedia (hanya saat commit dan
            // hanya untuk baris valid, agar preview tidak menulis apa pun).
            if ($persistMaster && empty($errors)) {
                if ($data['divisi'] !== '' && !in_array($data['divisi'], $masterDivisi, true)) {
                    MasterData::create(['type' => 'divisi', 'name' => $data['divisi'], 'is_active' => true]);
                    $masterDivisi[] = $data['divisi'];
                }
                if ($data['jabatan'] !== '' && !in_array($data['jabatan'], $masterJabatan, true)) {
                    MasterData::create(['type' => 'jabatan', 'name' => $data['jabatan'], 'is_active' => true]);
                    $masterJabatan[] = $data['jabatan'];
                }
                if ($data['golongan'] !== null && !in_array($data['golongan'], $masterGolongan, true)) {
                    MasterData::create(['type' => 'golongan', 'name' => $data['golongan'], 'is_active' => true]);
                    $masterGolongan[] = $data['golongan'];
                }
            }

            $result[] = [
                'row' => $row['row'],
                'data' => $data,
                'valid' => empty($errors),
                'errors' => $errors,
            ];
        }

        return $result;
    }

    private function parseDate(string $value): ?string
    {
        $value = trim($value);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'j/n/Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
            if ($date !== false && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        // Fallback longgar (mis. "1 Januari 2024").
        try {
            $date = new \DateTimeImmutable($value);

            return $date->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function summarize(array $validated): array
    {
        $valid = 0;
        $invalid = 0;

        foreach ($validated as $row) {
            $row['valid'] ? $valid++ : $invalid++;
        }

        return ['valid' => $valid, 'invalid' => $invalid, 'total' => $valid + $invalid];
    }
}
