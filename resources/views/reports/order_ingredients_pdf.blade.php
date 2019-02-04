<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">

<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
    }
    p {
        margin-bottom: 4pt;
        margin-top: 0pt;
    }
    table {
        font-family: Arial, sans-serif;
        font-size: 12pt;
        line-height: 1.2;
        margin-top: 2pt;
        margin-bottom: 5pt;
        border-collapse: collapse;
        width: 100%;
    }
    thead {
        font-weight: bold;
        vertical-align: bottom;
    }
    tfoot {
        font-weight: bold;
        vertical-align: top;
    }
    thead td {
        font-weight: bold;
    }
    tfoot td {
        font-weight: bold;
    }
    thead td, thead th, tfoot td, tfoot th {
        font-variant: small-caps;
    }
    .headerrow td, .headerrow th {
        background-gradient: linear #b7cebd #f5f8f5 0 1 0 0.2;
    }
    .footerrow td, .footerrow th {
        background-gradient: linear #b7cebd #f5f8f5 0 1 0 0.2;
    }
    th {
        font-weight: bold;
        vertical-align: top;
        text-align: left;
        padding-left: 2mm;
        padding-right: 2mm;
        padding-top: 0.5mm;
        padding-bottom: 0.5mm;
    }
    td {
        padding-left: 2mm;
        vertical-align: top;
        text-align: left;
        padding-right: 2mm;
        padding-top: 0.5mm;
        padding-bottom: 0.5mm;
    }
    th p {
        text-align: left;
        margin: 0pt;
    }
    td p {
        text-align: left;
        margin: 0pt;
    }
    table.widecells td {
        padding-left: 5mm;
        padding-right: 5mm;
    }
    table.tallcells td {
        padding-top: 3mm;
        padding-bottom: 3mm;
    }
    hr {
        width: 70%;
        height: 1px;
        text-align: center;
        color: #999999;
        margin-top: 8pt;
        margin-bottom: 8pt;
    }
    a {
        color: #000066;
        font-style: normal;
        text-decoration: underline;
        font-weight: normal;
    }
    ul {
        text-indent: 5mm;
        margin-bottom: 9pt;
    }
    ol {
        text-indent: 5mm;
        margin-bottom: 9pt;
    }
    pre {
        font-family: sans-serif;
        font-size: 9pt;
        margin-top: 5pt;
        margin-bottom: 5pt;
    }
    h1 {
        font-weight: normal;
        font-size: 26pt;
        color: #000066;
        font-family: sans-serif;
        margin-top: 18pt;
        margin-bottom: 6pt;
        border-top: 0.075cm solid #000000;
        border-bottom: 0.075cm solid #000000;
        text-align: ; page-break-after:avoid;
    }
    h2 {
        font-weight: bold;
        font-size: 12pt;
        color: #000066;
        font-family: sans-serif;
        margin-top: 6pt;
        margin-bottom: 6pt;
        border-top: 0.07cm solid #000000;
        border-bottom: 0.07cm solid #000000;
        text-align: ;  text-transform:uppercase;
        page-break-after: avoid;
    }
    h3 {
        font-weight: normal;
        font-size: 26pt;
        color: #000000;
        font-family: sans-serif;
        margin-top: 0pt;
        margin-bottom: 6pt;
        border-top: 0;
        border-bottom: 0;
        text-align: ; page-break-after:avoid;
    }
    h4 {
        font-weight: ; font-size: 13pt;
        color: #9f2b1e;
        font-family: sans-serif;
        margin-top: 10pt;
        margin-bottom: 7pt;
        font-variant: small-caps;
        text-align: ;  margin-collapse:collapse;
        page-break-after: avoid;
    }
    h5 {
        font-weight: bold;
        font-style: italic;
        ; font-size: 11pt;
        color: #000044;
        font-family: sans-serif;
        margin-top: 8pt;
        margin-bottom: 4pt;
        text-align: ;  page-break-after:avoid;
    }
    h6 {
        font-weight: bold;
        font-size: 9.5pt;
        color: #333333;
        font-family: sans-serif;
        margin-top: 6pt;
        margin-bottom: ;
                    text-align:;
        page-break-after: avoid;
    }
    .breadcrumb {
        text-align: right;
        font-size: 8pt;
        font-family: sans-serif;
        color: #666666;
        font-weight: bold;
        font-style: normal;
        margin-bottom: 6pt;
    }
    .evenrow td, .evenrow th {
        background-color: #fff;
    }
    .oddrow td, .oddrow th {
        background-color: #c8ced3;
    }
</style>

</head>

<body>
  <h1>Order Ingredients</h1>
  <h2>{{ date(DATE_RFC2822) }}</h2>

  <table border="1">
    <tbody>
      @foreach($data as $i => $row)
      <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
        @foreach($row as $value)
          <td>{{ $value }}</td>
        @endforeach
      </tr>
      @endforeach
    </tbody>
  
  </table>
</body>

</html>