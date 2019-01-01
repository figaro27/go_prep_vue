<html>

<head>
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
        font-size: 10pt;
        line-height: 1.2;
        margin-top: 2pt;
        margin-bottom: 5pt;
        border-collapse: collapse;
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
        font-family: DejaVuSansMono;
        font-size: 9pt;
        margin-top: 5pt;
        margin-bottom: 5pt;
    }
    h1 {
        font-weight: normal;
        font-size: 26pt;
        color: #000066;
        font-family: DejaVuSansCondensed;
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
        font-family: DejaVuSansCondensed;
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
        font-family: DejaVuSansCondensed;
        margin-top: 0pt;
        margin-bottom: 6pt;
        border-top: 0;
        border-bottom: 0;
        text-align: ; page-break-after:avoid;
    }
    h4 {
        font-weight: ; font-size: 13pt;
        color: #9f2b1e;
        font-family: DejaVuSansCondensed;
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
        font-family: DejaVuSansCondensed;
        margin-top: 8pt;
        margin-bottom: 4pt;
        text-align: ;  page-break-after:avoid;
    }
    h6 {
        font-weight: bold;
        font-size: 9.5pt;
        color: #333333;
        font-family: DejaVuSansCondensed;
        margin-top: 6pt;
        margin-bottom: ;
                    text-align:;
        page-break-after: avoid;
    }
    .breadcrumb {
        text-align: right;
        font-size: 8pt;
        font-family: DejaVuSerifCondensed;
        color: #666666;
        font-weight: bold;
        font-style: normal;
        margin-bottom: 6pt;
    }
    .evenrow td, .evenrow th {
        background-color: #f5f8f5;
    }
    .oddrow td, .oddrow th {
        background-color: #e3ece4;
    }
    .bpmTopic {
        background-color: #e3ece4;
    }
    .bpmTopicC {
        background-color: #e3ece4;
    }
    .bpmNoLines {
        background-color: #e3ece4;
    }
    .bpmNoLinesC {
        background-color: #e3ece4;
    }
    .bpmClear {
    }
    .bpmClearC {
        text-align: center;
    }
    .bpmTopnTail {
        background-color: #e3ece4;
        topntail: 0.02cm solid #495b4a;
    }
    .bpmTopnTailC {
        background-color: #e3ece4;
        topntail: 0.02cm solid #495b4a;
    }
    .bpmTopnTailClear {
        topntail: 0.02cm solid #495b4a;
    }
    .bpmTopnTailClearC {
        topntail: 0.02cm solid #495b4a;
    }
    .bpmTopicC td, .bpmTopicC td p {
        text-align: center;
    }
    .bpmNoLinesC td, .bpmNoLinesC td p {
        text-align: center;
    }
    .bpmClearC td, .bpmClearC td p {
        text-align: center;
    }
    .bpmTopnTailC td, .bpmTopnTailC td p {
        text-align: center;
    }
    .bpmTopnTailClearC td, .bpmTopnTailClearC td p {
        text-align: center;
    }
    .pmhMiddleCenter {
        text-align: center;
        vertical-align: middle;
    }
    .pmhMiddleRight {
        text-align: right;
        vertical-align: middle;
    }
    .pmhBottomCenter {
        text-align: center;
        vertical-align: bottom;
    }
    .pmhBottomRight {
        text-align: right;
        vertical-align: bottom;
    }
    .pmhTopCenter {
        text-align: center;
        vertical-align: top;
    }
    .pmhTopRight {
        text-align: right;
        vertical-align: top;
    }
    .pmhTopLeft {
        text-align: left;
        vertical-align: top;
    }
    .pmhBottomLeft {
        text-align: left;
        vertical-align: bottom;
    }
    .pmhMiddleLeft {
        text-align: left;
        vertical-align: middle;
    }
    .infobox {
        margin-top: 10pt;
        background-color: #DDDDBB;
        text-align: center;
        border: 1px solid #880000;
    }
    .bpmTopic td, .bpmTopic th {
        border-top: 1px solid #FFFFFF;
    }
    .bpmTopicC td, .bpmTopicC th {
        border-top: 1px solid #FFFFFF;
    }
    .bpmTopnTail td, .bpmTopnTail th {
        border-top: 1px solid #FFFFFF;
    }
    .bpmTopnTailC td, .bpmTopnTailC th {
        border-top: 1px solid #FFFFFF;
    }
</style>

</head>

<body>
  <h1>Order Ingredients</h1>
  <h2>{{ date(DATE_RFC2822) }}</h2>

  <table border="1">
    <tbody>
      @foreach($data as $row)
      <tr>
        @foreach($row as $value)
          <td>{{ $value }}</td>
        @endforeach
      </tr>
      @endforeach
    </tbody>
  
  </table>
</body>

</html>