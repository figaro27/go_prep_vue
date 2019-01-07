<?php

namespace App\Utils\Data;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use \XLSXWriter;
use mikehaertl\wkhtmlto\Pdf;

trait ExportsData
{
    abstract function exportData();
    abstract function exportPdfView();

    public function export(Request $request)
    {
        $type = $request->route('type');

        if (!in_array($type, ['pdf', 'csv', 'xls'])) {
            return response()->json([
                'message' => 'Invalid export format',
                500,
            ]);
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
        return response()->json([
            'url' => Storage::url($filename),
        ]);
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
        return response()->json([
            'url' => Storage::url($filename),
        ]);
    }

    public function handlePdf($data)
    {
        $filename = 'public/' . md5(time()) . '.pdf';

        $html = view($this->exportPdfView(), ['data' => $data])->render();

        $pdf = new Pdf([
          'encoding' => 'UTF-8',
        ]);
        $pdf->addPage($html);
        $output = $pdf->toString();

        Storage::disk('local')->put($filename, $output);
        return response()->json([
            'url' => Storage::url($filename),
        ]);
    }
}
