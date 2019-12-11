<?php

namespace App\Exportable;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use mikehaertl\wkhtmlto\Pdf;
use \XLSXWriter;
use Illuminate\Support\Facades\Log;

trait Exportable
{
    protected $orientation = 'landscape';

    /** @var Collection $params */
    protected $params = null;

    /** @var string $type */
    protected $type = null;

    /** @var int $page */
    protected $page = 1;

    /** @var int $perPage */
    protected $perPage = 25;

    abstract public function exportData($type = null);
    abstract public function exportPdfView();

    public function export($type)
    {
        if (!in_array($type, ['pdf', 'csv', 'xls'])) {
            return null;
        }

        $this->type = $type;

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

    /**
     * Manipulate vars that get passed to twig
     */
    public function filterVars($vars)
    {
        return $vars;
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

        $data = collect($data)->toArray();

        $writer->writeSheet($data);
        $output = $writer->writeToString();

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);
    }

    public function handlePdf($data)
    {
        $filename = 'public/' . md5(time()) . '.pdf';

        $vars = $this->filterVars([
            'data' => $data,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation])
        ]);

        $html = view($this->exportPdfView(), $vars)->render();

        $pdfConfig = [
            'encoding' => 'utf-8',
            'orientation' => $this->orientation,
            'page-size' => 'Letter',
            'no-outline',
            //'margin-top' => 0,
            //'margin-bottom' => 0,
            //'margin-left' => 0,
            //'margin-right' => 0,
            //'binary' => '/usr/local/bin/wkhtmltopdf',
            'disable-smart-shrinking'
        ];

        if (config('pdf.xserver')) {
            $pdfConfig = array_merge($pdfConfig, [
                'use-xserver',
                'commandOptions' => array(
                    'enableXvfb' => true
                )
            ]);
        }

        $pdf = new Pdf($pdfConfig);
        $pdf->addPage($html);

        $output = $pdf->toString();

        if ($pdf->getError()) {
            Log::error('PDF Error: ' . $pdf->getError());
        }

        /*if ($output === false) {
            var_dump($pdf->getError());
            exit();
        }*/

        Storage::disk('local')->put($filename, $output);
        return Storage::url($filename);
    }

    public function getDeliveryDates($defaultFuture = true)
    {
        $dates = [];

        if ($this->params && $this->params->has('delivery_dates')) {
            $dates = json_decode($this->params->get('delivery_dates'));

            if ($dates->to == null) {
                $dates->to = $dates->from;
            }

            $dates = [
                'from' => Carbon::parse($dates->from)->startOfDay(),
                'to' => Carbon::parse($dates->to)->endOfDay()
            ];
        } elseif ($defaultFuture) {
            $dates = [
                'from' => Carbon::today()->startOfDay(),
                'to' => Carbon::today()
                    ->addDays(6)
                    ->startOfDay()
            ];
        }

        return $dates;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }
}
