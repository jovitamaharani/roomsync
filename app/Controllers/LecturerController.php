<?php
require_once "Config/Database.php";
require_once "app/Models/Lecturer.php";

class LecturerController
{
    private $lecturerModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $database = new Database();
        $db = $database->getConnection();
        $this->lecturerModel = new Lecturer($db);
    }

    private function validateField($field, $value)
    {
        if ($field === 'kode') {
            if ($value === '') {
                return "Kode dosen wajib diisi.";
            }
            if (mb_strlen($value, 'UTF-8') > 9) {
                return "Kode dosen maksimal 9 karakter.";
            }
            return null;
        }

        if ($field === 'name') {
            if ($value === '') {
                return "Nama dosen wajib diisi.";
            }
            if (mb_strlen($value, 'UTF-8') > 100) {
                return "Nama dosen maksimal 100 karakter.";
            }
            return null;
        }

        if ($field === 'telepon') {
            if ($value === '') {
                return "Nomor telepon wajib diisi.";
            }
            if (mb_strlen($value, 'UTF-8') > 20) {
                return "Nomor telepon maksimal 20 karakter.";
            }
            $digitsOnly = preg_replace('/\D+/', '', $value);
            if (!preg_match('/^\+?[0-9\s\-()]+$/', $value) || strlen($digitsOnly) < 6) {
                return "Format nomor telepon tidak valid.";
            }
            return null;
        }

        return null;
    }

    private function validate($kode, $name, $telepon)
    {
        return $this->validateField('kode', $kode)
            ?? $this->validateField('name', $name)
            ?? $this->validateField('telepon', $telepon);
    }

    private function redirect($type = null, $message = null, $target = null, $oldData = [])
    {
        if ($type && $message) {
            $_SESSION['flash'] = [
                'type' => $type,
                'message' => $message,
                'target' => $target,
                'old' => $oldData
            ];
        }
        header("Location: index.php?controller=lecturer&action=index");
        exit;
    }

    public function index()
    {
        $lecturers = $this->lecturerModel->getAll();
        require_once "app/Views/admin/lecturer/index.php";
    }

    public function create()
    {
        require_once "app/Views/admin/lecturer/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kode = trim($_POST['kode'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $telepon = trim($_POST['telepon'] ?? '');

            $oldData = [
                'kode' => $kode,
                'name' => $name,
                'telepon' => $telepon
            ];

            $error = $this->validate($kode, $name, $telepon);
            if ($error) {
                $this->redirect('error', $error, 'add', $oldData);
            }

            try {
                $this->lecturerModel->create($kode, $name, $telepon);
                $this->redirect('success', 'Dosen berhasil ditambahkan.');
            } catch (PDOException $e) {
                if ($e->getCode() === '23505') {
                    $this->redirect('error', 'Kode dosen sudah dipakai.', 'add', $oldData);
                }
                throw $e;
            }
        }
        $this->redirect();
    }

    public function edit($id)
    {
        $lecturers = $this->lecturerModel->getById($id);
        if (!$lecturers) {
            $this->redirect('error', 'Data tidak ditemukan.');
        }
        require_once "app/Views/admin/lecturer/index.php";
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $kode = trim($_POST['kode'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $telepon = trim($_POST['telepon'] ?? '');

            if ($id === '') {
                $this->redirect('error', 'ID tidak valid.');
            }

            $oldData = [
                'id' => $id,
                'kode' => $kode,
                'name' => $name,
                'telepon' => $telepon
            ];

            $error = $this->validate($kode, $name, $telepon);
            if ($error) {
                $this->redirect('error', $error, 'edit', $oldData);
            }

            try {
                $this->lecturerModel->update($id, $kode, $name, $telepon);
                $this->redirect('success', 'Dosen berhasil diperbarui.');
            } catch (PDOException $e) {
                if ($e->getCode() === '23505') {
                    $this->redirect('error', 'Kode dosen sudah dipakai.', 'edit', $oldData);
                }
                throw $e;
            }
        }
        $this->redirect();
    }

    public function delete($id = null)
    {
        $id = $_POST['id'] ?? $id ?? $_GET['id'] ?? null;

        if (empty($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        try {
            $this->lecturerModel->delete($id);
            $this->redirect('success', 'Dosen berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Dosen tidak bisa dihapus karena masih dipakai oleh data lain.');
            }
            throw $e;
        }
    }

    private function parseXlsxRows($filePath)
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('Ekstensi PHP ZipArchive tidak aktif untuk membaca berkas .xlsx.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('Berkas .xlsx tidak dapat dibuka.');
        }

        // 1. Ambil sharedStrings.xml jika ada (teks di Excel sering disimpan di sini)
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sx = simplexml_load_string($sharedXml);
            if ($sx) {
                foreach ($sx->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } else {
                        $text = '';
                        foreach ($si->r as $run) {
                            $text .= (string) $run->t;
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }
        }

        // 2. Baca sheet pertama (xl/worksheets/sheet1.xml)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Lembar kerja (sheet1) tidak ditemukan di dalam berkas .xlsx.');
        }

        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowNum = (int) ($row['r'] ?? (count($rows) + 1));
            $cells = [];

            foreach ($row->c as $c) {
                $cellRef = (string) ($c['r'] ?? '');
                $colLetters = preg_replace('/\d+/', '', $cellRef);
                $colIndex = $this->colLettersToIndex($colLetters);
                if ($colIndex === null) {
                    continue;
                }

                $type = (string) ($c['t'] ?? '');
                $val = '';

                if ($type === 's') {
                    $idx = (int) ($c->v ?? -1);
                    $val = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string) $c->is->t;
                } else {
                    $val = isset($c->v) ? (string) $c->v : '';
                }

                $cells[$colIndex] = trim($val);
            }

            if (!empty($cells)) {
                ksort($cells);
                // Ratakan ke array 0-based [col0, col1, col2]
                $maxCol = max(array_keys($cells));
                $normalized = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $normalized[] = $cells[$i] ?? '';
                }
                $rows[] = [
                    'row' => $rowNum,
                    'cells' => $normalized,
                ];
            }
        }

        return $rows;
    }

    private function colLettersToIndex($letters)
    {
        $letters = strtoupper(trim($letters));
        if ($letters === '') {
            return null;
        }
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $charCode = ord($letters[$i]);
            if ($charCode < 65 || $charCode > 90) {
                return null;
            }
            $index = $index * 26 + ($charCode - 64);
        }
        return $index - 1; // A -> 0, B -> 1, C -> 2
    }

    public function import()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        if (empty($_FILES['file']) || ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->redirect('error', 'Berkas gagal diunggah atau belum dipilih.', 'import');
        }

        $originalName = $_FILES['file']['name'] ?? '';
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $tmpPath = $_FILES['file']['tmp_name'];

        try {
            $rawRows = [];

            if ($ext === 'xlsx') {
                $parsed = $this->parseXlsxRows($tmpPath);
                foreach ($parsed as $item) {
                    $rawRows[] = [
                        'row' => $item['row'],
                        'cells' => $item['cells'],
                    ];
                }
            } elseif ($ext === 'csv') {
                $handle = fopen($tmpPath, 'r');
                if (!$handle) {
                    $this->redirect('error', 'Tidak dapat membaca berkas CSV.', 'import');
                }
                $firstLine = fgets($handle);
                if ($firstLine === false) {
                    fclose($handle);
                    $this->redirect('error', 'Berkas CSV kosong.', 'import');
                }
                $cleanFirst = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
                $delimiter = (substr_count($cleanFirst, ';') > substr_count($cleanFirst, ',')) ? ';' : ',';
                rewind($handle);

                $rowNum = 0;
                while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $rowNum++;
                    if (isset($data[0])) {
                        $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $data[0]);
                    }
                    $rawRows[] = [
                        'row' => $rowNum,
                        'cells' => array_map(fn($v) => trim((string) $v), $data),
                    ];
                }
                fclose($handle);
            } elseif ($ext === 'xls') {
                $this->redirect('error', 'Format .xls lama belum didukung. Silakan simpan sebagai .xlsx (atau .csv).', 'import');
            } else {
                $this->redirect('error', 'Format berkas harus .xlsx (atau .csv).', 'import');
            }

            $validRows = [];
            $seenInFile = []; // [kode => baris_pertama]
            $isFirstRow = true;

            // 1. Validasi format tiap kolom & duplikat di dalam file
            foreach ($rawRows as $r) {
                $rowNum = $r['row'];
                $cells = $r['cells'];

                $kode = trim((string) ($cells[0] ?? ''));
                $name = trim((string) ($cells[1] ?? ''));
                $telepon = trim((string) ($cells[2] ?? ''));

                // Lewati baris kosong
                if ($kode === '' && $name === '' && $telepon === '') {
                    continue;
                }

                // Lewati baris header jika baris pertama berisi header
                if ($isFirstRow) {
                    $isFirstRow = false;
                    if (in_array(strtolower($kode), ['kode', 'kode dosen', 'kode_dosen'], true)) {
                        continue;
                    }
                }

                if ($errKode = $this->validateField('kode', $kode)) {
                    $this->redirect('error', "Baris {$rowNum} (Kode Dosen): {$errKode}", 'import');
                }
                if ($errName = $this->validateField('name', $name)) {
                    $this->redirect('error', "Baris {$rowNum} (Nama Dosen): {$errName}", 'import');
                }
                if ($errTel = $this->validateField('telepon', $telepon)) {
                    $this->redirect('error', "Baris {$rowNum} (Nomor Telepon): {$errTel}", 'import');
                }

                if (isset($seenInFile[$kode])) {
                    $firstRow = $seenInFile[$kode];
                    $this->redirect(
                        'error',
                        "Baris {$rowNum} (Kode Dosen '{$kode}'): Duplikat di dalam berkas (sudah muncul di baris {$firstRow}). Impor dibatalkan.",
                        'import'
                    );
                }
                $seenInFile[$kode] = $rowNum;

                $validRows[] = [
                    'row' => $rowNum,
                    'kode' => $kode,
                    'name' => $name,
                    'telepon' => $telepon,
                ];
            }

            if (empty($validRows)) {
                $this->redirect('error', 'Tidak ada data dosen valid yang ditemukan di dalam berkas.', 'import');
            }

            // 2. Cek apakah ada kode dosen yang sudah ada di database sebelum insert
            $allKodes = array_column($validRows, 'kode');
            $existingInDb = $this->lecturerModel->findExistingKodes($allKodes);
            if (!empty($existingInDb)) {
                $existingMap = array_flip($existingInDb);
                $conflictRows = [];
                foreach ($validRows as $item) {
                    if (isset($existingMap[$item['kode']])) {
                        $conflictRows[] = "Baris {$item['row']} ({$item['kode']})";
                    }
                }
                $this->redirect(
                    'error',
                    "Kode dosen sudah terdaftar di database pada " . implode(', ', $conflictRows) . ". Impor dibatalkan.",
                    'import'
                );
            }

            // 3. Semua valid & bebas duplikat -> simpan dalam transaksi
            $this->lecturerModel->beginTransaction();
            foreach ($validRows as $item) {
                $this->lecturerModel->create($item['kode'], $item['name'], $item['telepon']);
            }
            $this->lecturerModel->commit();

            $this->redirect('success', count($validRows) . ' data dosen berhasil diimpor.');
        } catch (Throwable $e) {
            $this->lecturerModel->rollBack();

            if ($e instanceof PDOException && $e->getCode() === '23505') {
                $this->redirect('error', 'Terjadi konflik kode dosen duplikat saat menyimpan. Impor dibatalkan.', 'import');
            }
            if ($e instanceof RuntimeException) {
                $this->redirect('error', $e->getMessage(), 'import');
            }
            throw $e;
        }
    }
}