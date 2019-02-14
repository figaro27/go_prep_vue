<?php

namespace App\Exportable;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use mikehaertl\wkhtmlto\Pdf;
use \XLSXWriter;

trait Exportable
{
    protected $orientation = 'landscape';

    abstract public function exportData($type = null);
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

        $pdfConfig = ['encoding' => 'utf-8', 'orientation' => $this->orientation];

        if(config('pdf.xserver')) {
          $pdfConfig = array_merge($pdfConfig, [
            'use-xserver',
            'commandOptions' => array(
                'procEnv' => array('DISPLAY' => ':0'),
            ),
          ]);
        }

        $pdf = new Pdf($pdfConfig);
        $pdf->addPage($html);
        $output = $pdf->toString();

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);
    }

    public function getDeliveryDates()
    {
        $dates = [];

        if ($this->params->has('delivery_dates')) {
            $dates = json_decode($this->params->get('delivery_dates'));

            $dates = [
              'from' => Carbon::parse($dates->from),
              'to' => Carbon::parse($dates->to),
            ];
        }

        return $dates;
    }
}
