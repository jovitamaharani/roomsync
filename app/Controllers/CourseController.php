<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/Course.php";

class CourseController
{
    private $courseModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $db = (new Database())->getConnection();
        $this->courseModel = new Course($db);
    }

    private function redirect($type = null, $message = null, $target = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = [
                'type' => $type,
                'message' => $message,
                'target' => $target,
            ];
        }
        header("Location: index.php?controller=course&action=index");
        exit;
    }

    // Gagal: kembali ke index, modal dibuka lagi dengan isian lama
    private function fail($message, array $data, $modal)
    {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => $message,
            'target' => $modal,
        ];
        $_SESSION['old'] = ['modal' => $modal, 'data' => $data];
        header("Location: index.php?controller=course&action=index");
        exit;
    }
    private function isUuid($id)
    {
        return is_string($id) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $id
        ) === 1;
    }

    private function collect()
    {
        return [
            'kode_mk' => strtoupper(trim($_POST['kode_mk'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
        ];
    }

    private function validate(array $d, $field = null)
    {
        if ($field === null || $field === 'kode_mk') {
            $kode = $d['kode_mk'] ?? '';
            if ($kode === '' || mb_strlen($kode, 'UTF-8') > 20) {
                return "Kode mata kuliah wajib diisi (maksimal 20 karakter).";
            }
        }

        if ($field === null || $field === 'name') {
            $name = $d['name'] ?? '';
            if ($name === '' || mb_strlen($name, 'UTF-8') > 150) {
                return "Nama mata kuliah wajib diisi (maksimal 150 karakter).";
            }
        }

        return null;
    }

    private function dbErrorMessage(PDOException $e)
    {
        $messages = [
            '23505' => 'Kode mata kuliah sudah terdaftar.',
            '23502' => 'Ada kolom wajib yang belum diisi.',
            '22P02' => 'Format data tidak valid.',
        ];
        return $messages[$e->getCode()] ?? null;
    }

    public function index()
    {
        $courses = $this->courseModel->getAll();
        require_once __DIR__ . "/../Views/admin/courses/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $data = $this->collect();
        if ($error = $this->validate($data)) {
            $this->fail($error, $data, 'add');
        }

        try {
            $this->courseModel->create($data['kode_mk'], $data['name']);
            $this->redirect('success', 'Mata kuliah berhasil ditambahkan.');
        } catch (PDOException $e) {
            if ($message = $this->dbErrorMessage($e)) {
                $this->fail($message, $data, 'add');
            }
            throw $e;
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $id = $_POST['id'] ?? '';
        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        $data = $this->collect();
        $old = ['id' => $id] + $data;

        if ($error = $this->validate($data)) {
            $this->fail($error, $old, 'edit');
        }

        try {
            if (!$this->courseModel->update($id, $data['kode_mk'], $data['name'])) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Mata kuliah berhasil diperbarui.');
        } catch (PDOException $e) {
            if ($message = $this->dbErrorMessage($e)) {
                $this->fail($message, $old, 'edit');
            }
            throw $e;
        }
    }

    public function delete($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('error', 'Permintaan tidak valid.');
        }

        $id = $_POST['id'] ?? $id;
        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        try {
            if (!$this->courseModel->delete($id)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Mata kuliah berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Mata kuliah tidak bisa dihapus karena sudah dipakai pada jadwal.');
            }
            throw $e;
        }
    }

    private function colLettersToIndex($letters)
    {
        $letters = strtoupper(trim((string) $letters));
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
        return $index - 1; // A -> 0, B -> 1
    }

    private function parseXlsxRows(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('Ekstensi ZipArchive PHP belum aktif untuk membaca berkas .xlsx.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException('Berkas .xlsx tidak dapat dibuka atau rusak.');
        }

        try {
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
                            foreach ($si->r as $r) {
                                $text .= (string) $r->t;
                            }
                            $sharedStrings[] = $text;
                        }
                    }
                }
            }

            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml === false) {
                throw new RuntimeException('Struktur sheet utama pada berkas .xlsx tidak ditemukan.');
            }

            $sheet = simplexml_load_string($sheetXml);
            if (!$sheet || !isset($sheet->sheetData->row)) {
                return [];
            }

            $rows = [];
            $fallbackRow = 0;
            foreach ($sheet->sheetData->row as $rowNode) {
                $fallbackRow++;
                $attr = $rowNode->attributes();
                $rowNum = isset($attr['r']) ? (int) $attr['r'] : $fallbackRow;

                $cellsByCol = [];
                foreach ($rowNode->c as $cNode) {
                    $cAttr = $cNode->attributes();
                    $cellRef = (string) ($cAttr['r'] ?? '');
                    $colLetters = preg_replace('/\d+/', '', $cellRef);
                    $colIdx = $this->colLettersToIndex($colLetters);
                    if ($colIdx === null || $colIdx < 0) {
                        continue;
                    }

                    $type = (string) ($cAttr['t'] ?? '');
                    $val = '';

                    if ($type === 'inlineStr' && isset($cNode->is->t)) {
                        $val = (string) $cNode->is->t;
                    } elseif (isset($cNode->v)) {
                        $raw = (string) $cNode->v;
                        if ($type === 's') {
                            $idx = (int) $raw;
                            $val = $sharedStrings[$idx] ?? '';
                        } else {
                            $val = $raw;
                        }
                    }

                    $cellsByCol[$colIdx] = trim($val);
                }

                if (empty($cellsByCol)) {
                    continue;
                }

                $maxCol = max(array_keys($cellsByCol));
                $rowCells = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $rowCells[] = $cellsByCol[$i] ?? '';
                }

                $rows[] = [
                    'row' => $rowNum,
                    'cells' => $rowCells,
                ];
            }

            return $rows;
        } finally {
            $zip->close();
        }
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
            $seenInFile = []; // [kode_mk => baris_pertama]
            $isFirstRow = true;

            // 1. Validasi header template, format tiap kolom & duplikat di dalam file
            foreach ($rawRows as $r) {
                $rowNum = $r['row'];
                $cells = $r['cells'];

                $kodeMk = strtoupper(trim((string) ($cells[0] ?? '')));
                $name = trim((string) ($cells[1] ?? ''));
                $extraCol3 = trim((string) ($cells[2] ?? ''));

                // Lewati baris kosong
                if ($kodeMk === '' && $name === '' && $extraCol3 === '') {
                    continue;
                }

                // Baris pertama wajib berupa header resmi template mata kuliah
                if ($isFirstRow) {
                    $isFirstRow = false;
                    $h1 = strtolower($kodeMk);
                    $h2 = strtolower($name);
                    if ($h1 !== 'kode_mk' || $h2 !== 'name' || $extraCol3 !== '') {
                        $this->redirect(
                            'error',
                            'Format berkas tidak sesuai template Mata Kuliah. Pastikan header kolom 1 adalah "kode_mk" dan kolom 2 adalah "name".',
                            'import'
                        );
                    }
                    continue;
                }

                // Tolak jika ada data di kolom ke-3 (indikasi salah pakai file modul lain)
                if ($extraCol3 !== '') {
                    $this->redirect(
                        'error',
                        "Baris {$rowNum}: Terdeteksi kolom berlebih di luar template Mata Kuliah. Pastikan menggunakan template Mata Kuliah.",
                        'import'
                    );
                }

                if ($errKode = $this->validate(['kode_mk' => $kodeMk, 'name' => 'VALID'], 'kode_mk')) {
                    $this->redirect('error', "Baris {$rowNum} (Kode MK): {$errKode}", 'import');
                }
                if ($errName = $this->validate(['kode_mk' => 'VALID', 'name' => $name], 'name')) {
                    $this->redirect('error', "Baris {$rowNum} (Nama Mata Kuliah): {$errName}", 'import');
                }

                if (isset($seenInFile[$kodeMk])) {
                    $firstRow = $seenInFile[$kodeMk];
                    $this->redirect(
                        'error',
                        "Baris {$rowNum} (Kode MK '{$kodeMk}'): Duplikat di dalam berkas (sudah muncul di baris {$firstRow}). Impor dibatalkan.",
                        'import'
                    );
                }
                $seenInFile[$kodeMk] = $rowNum;

                $validRows[] = [
                    'row' => $rowNum,
                    'kode_mk' => $kodeMk,
                    'name' => $name,
                ];
            }

            if (empty($validRows)) {
                $this->redirect('error', 'Tidak ada data mata kuliah valid yang ditemukan di dalam berkas.', 'import');
            }

            // 2. Cek apakah ada kode_mk yang sudah ada di database sebelum insert
            $allKodes = array_column($validRows, 'kode_mk');
            $existingInDb = $this->courseModel->findExistingKodes($allKodes);
            if (!empty($existingInDb)) {
                $existingMap = array_flip(array_map('strtoupper', $existingInDb));
                $conflictRows = [];
                foreach ($validRows as $item) {
                    if (isset($existingMap[$item['kode_mk']])) {
                        $conflictRows[] = "Baris {$item['row']} ({$item['kode_mk']})";
                    }
                }
                $this->redirect(
                    'error',
                    "Kode MK sudah terdaftar di database pada " . implode(', ', $conflictRows) . ". Impor dibatalkan.",
                    'import'
                );
            }

            // 3. Semua valid & bebas duplikat -> simpan dalam transaksi
            $this->courseModel->beginTransaction();
            foreach ($validRows as $item) {
                $this->courseModel->create($item['kode_mk'], $item['name']);
            }
            $this->courseModel->commit();

            $this->redirect('success', count($validRows) . ' data mata kuliah berhasil diimpor.');
        } catch (Throwable $e) {
            $this->courseModel->rollBack();

            if ($e instanceof PDOException && $e->getCode() === '23505') {
                $this->redirect('error', 'Terjadi konflik Kode MK duplikat saat menyimpan. Impor dibatalkan.', 'import');
            }
            if ($e instanceof RuntimeException) {
                $this->redirect('error', $e->getMessage(), 'import');
            }
            throw $e;
        }
    }
}