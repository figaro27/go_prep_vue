<?php

namespace App\Exportable;

use App\Utils\Data\ExportsData;
use Illuminate\Support\Facades\Storage;
use \XLSXWriter;
use mikehaertl\wkhtmlto\Pdf;

trait Exportable
{
    abstract public function exportData();
    abstract public function exportPdfView();

    public function export($type)
    {
        if (!in_array($type, ['pdf', 'csv', 'xls'])) {
            return null;
        }

        $data = $this->exportData($type);

        switch ($type) {
            case 'csv':
                return $this->handleCsv($data);
                break;

            case 'xls':
                return $this->handleXls($data);
                break;

            case 'pdf':
                return $this->handlePdf($data);
                break;
        }
    }

    public function handleCsv($data)
    {
        $filename = 'public/' . md5(time()) . '.csv';

        /*
        $rows = array_merge([
        [
        'Name', 'Company', 'Address', 'Address 2',
        'City', 'Province', 'Postal Code', 'Country',
        'Email', 'Phone',
        ],
        ], $rows);*/

        $output = $this->generateCsv($data);

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);
    }

    protected function generateCsv($data, $headers = false)
    {
        # Generate CSV data from array
        $fh = fopen('php://temp', 'rw'); # don't create a file, attempt
        # to use memory instead

        if ($headers) {
            # write out the headers
            fputcsv($fh, array_keys(current($data)));
        }

        # write out the data
        foreach ($data as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

    public function handleXls($data)
    {
        $filename = 'public/' . md5(time()) . '.xlsx';

        $writer = new XLSXWriter();

        $writer->writeSheet($data);
        $output = $writer->writeToString();

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);
    }

    public function handlePdf($data)
    {
        $filename = 'public/' . md5(time()) . '.pdf';

        $html = view($this->exportPdfView(), ['data' => $data])->render();

        $pdf = new Pdf([
            'encoding' => 'UTF-8',
            'orientation' => 'landscape',
        ]);
        $pdf->addPage($html);
        $output = $pdf->toString();

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);
    }
}
