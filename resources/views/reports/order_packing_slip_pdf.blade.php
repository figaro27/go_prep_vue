<!doctype html>
<html>

<head>
  <meta charset="utf-8">
    <title>Ori (blue)</title>
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <meta name="description" content="Invoicebus Invoice Template">
    <meta name="author" content="Invoicebus">

    <meta name="template-hash" content="05be92c117a5229e9fc1635fc3e08cbf">
  <style>
    /*! Invoice Templates @author: Invoicebus @email: info@invoicebus.com @web: https://invoicebus.com @version: 1.0.0 @updated: 2017-04-08 16:04:49 @license: Invoicebus */
/* Reset styles */
@import url("https://fonts.googleapis.com/css?family=Open+Sans:400,700&subset=cyrillic,cyrillic-ext,latin,greek-ext,greek,latin-ext,vietnamese");
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed,
figure, figcaption, footer, header, hgroup,
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
  margin: 0;
  padding: 0;
  border: 0;
  font: inherit;
  font-size: 100%;
  vertical-align: baseline;
}

html {
  line-height: 1;
}

ol, ul {
  list-style: none;
}

table {
  border-collapse: collapse;
  border-spacing: 0;
}

caption, th, td {
  text-align: left;
  font-weight: normal;
  vertical-align: middle;
}

q, blockquote {
  quotes: none;
}
q:before, q:after, blockquote:before, blockquote:after {
  content: "";
  content: none;
}

a img {
  border: none;
}

article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
  display: block;
}

/* Invoice styles */
/**
 * DON'T override any styles for the <html> and <body> tags, as this may break the layout.
 * Instead wrap everything in one main <div id="container"> element where you may change
 * something like the font or the background of the invoice
 */
html, body {
  /* MOVE ALONG, NOTHING TO CHANGE HERE! */
}

/** 
 * IMPORTANT NOTICE: DON'T USE '!important' otherwise this may lead to broken print layout.
 * Some browsers may require '!important' in oder to work properly but be careful with it.
 */
.clearfix {
  display: block;
  clear: both;
}

.hidden {
  display: none;
}

b, strong, .bold {
  font-weight: bold;
}

#container {
  font: normal 13px/1.4em 'Open Sans', Sans-serif;
  margin: 0 auto;
  min-height: 1158px;
  color: #58585b;
  position: relative;
  background: #f5f5f5;
}

.invoice-top {
  background: white;
}

.logo {
  float: left;
  margin: 0 40px;
}
.logo img {
  width: 200px;
  height: 200px;
}

#memo #invoice-title-number {
  float: left;
  margin: 40px 20px 15px 40px;
  max-width: 530px;
}
#memo #invoice-title-number span {
  min-width: 20px;
}
#memo #invoice-title-number #title {
  font-weight: bold;
  font-size: 110px;
  line-height: 1em;
  display: block;
  max-width: 460px;
}
#memo #invoice-title-number .company-name {
  float: left;
  font-weight: bold;
  font-size: 22px;
  line-height: 1em;
}
#memo #invoice-title-number #number {
  float: right;
  font-size: 14px;
  margin: 6px 0 0 20px;
}
#memo #invoice-info {
  float: right;
  margin-top: 60px;
}
#memo #invoice-info > div {
  text-align: right;
  padding: 0 40px;
  border-bottom: 1px solid #ddd;
  width: 240px;
  padding-left: 10px;
}
#memo #invoice-info > div > span {
  display: inline-block;
  min-width: 20px;
  min-height: 18px;
  margin-bottom: 3px;
}
#memo #invoice-info:after {
  content: '';
  display: block;
  clear: both;
}
#memo:after {
  content: '';
  display: block;
  clear: both;
}

.company-info {
  margin: 15px 0;
  background: #609BCB;
  color: white;
  padding: 5px 40px;
}
.company-info div {
  min-width: 20px;
}
.company-info table.company-table {
  width: 100%;
  max-width: 820px;
  table-layout: auto;
}
.company-info table.company-table tr td {
  vertical-align: middle;
}
.company-info table.company-table tr td table {
  table-layout: auto;
}
.company-info table.company-table tr td.second {
  padding: 0 20px;
}
.company-info table.company-table tr td:last-child table {
  float: right;
}
.company-info table.company-table tr td .info.large div {
  max-width: 235px;
}
.company-info table.company-table tr td .info.small div {
  max-width: 125px;
}
.company-info table.company-table tr td.icon {
  width: 28px;
  max-width: 28px;
  padding-right: 10px;
}
.company-info table.company-table tr td.icon > div {
  background-image: url("../img/icons.svg");
  background-repeat: no-repeat;
  background-size: 100px auto;
  width: 28px;
  height: 20px;
}
.company-info table.company-table tr td.icon .icon-mail {
  background-position: 0 0;
}
.company-info table.company-table tr td.icon .icon-globe {
  background-position: -35px 0;
}
.company-info table.company-table tr td.icon .icon-phone {
  background-position: -70px 0;
}

.right-side {
  float: right;
  margin-right: 40px;
}
.right-side .payment-info {
  max-width: 210px;
  min-width: 20px;
  float: left;
  text-align: right;
  margin: 48px 25px 0 0;
}
.right-side .payment-info div {
  margin-bottom: 5px;
}
.right-side #client-info {
  float: right;
  margin-top: 10px;
}
.right-side #client-info > div {
  margin-bottom: 5px;
}
.right-side #client-info span {
  display: block;
  width: 250px;
  max-width: 250px;
}
.right-side #client-info > span {
  display: block;
  margin-bottom: 20px;
}

.spacer {
  height: 15px;
}

table {
  table-layout: fixed;
}
table th, table td {
  vertical-align: top;
  word-break: keep-all;
  word-wrap: break-word;
}

#items .first-cell, #items table th:nth-child(2), #items table td:nth-child(2) {
  width: 18px;
  text-align: right;
}
#items .circle {
  width: 30px;
  padding: 0 !important;
  vertical-align: middle;
}
#items .circle div {
  display: block;
  -moz-border-radius: 50%;
  -webkit-border-radius: 50%;
  border-radius: 50%;
  width: 6px;
  height: 6px;
  background: #58585b;
}
#items table {
  border-collapse: separate;
  width: 100%;
  border-bottom: 3px solid #609BCB;
}
#items table th {
  font-weight: bold;
  padding: 10px 5px;
  text-align: right;
  background: #609BCB;
  color: white;
  text-transform: uppercase;
}
#items table th:nth-child(3) {
  width: 30%;
  text-align: left;
}
#items table th:nth-child(8) {
  text-align: right;
}
#items table td {
  padding: 10px 5px;
  text-align: right;
}
#items table td:nth-child(2) {
  text-indent: -9999px;
  text-align: left;
}
#items table td:nth-child(3) {
  text-align: left;
}
#items table .last {
  width: 40px !important;
  padding: 0 !important;
}

.currency {
  float: left;
  margin: 18px 0 0 40px;
  font-size: 11px;
}
.currency span {
  min-width: 20px;
  display: inline-block;
}
.currency .label {
  text-transform: uppercase;
}
.currency .value {
  font-weight: bold;
}

#sums {
  float: right;
  margin-top: 10px;
  page-break-inside: avoid;
}
#sums table tr th, #sums table tr td {
  min-width: 106px;
  padding: 8px 5px;
  text-align: right;
}
#sums table tr th {
  text-align: left;
  padding-right: 25px;
  padding-left: 40px;
  font-weight: bold;
}
#sums table tr td:last-child {
  width: 40px !important;
  min-width: 40px !important;
  padding: 0 !important;
}
#sums table tr.amount-total th {
  text-transform: uppercase;
}
#sums table tr.amount-total th, #sums table tr.amount-total td {
  border-top: 3px solid #609BCB;
  font-weight: bold;
}
#sums table tr:last-child th, #sums table tr:last-child td {
  border-top: 3px solid #609BCB;
  font-weight: bold;
}

#terms {
  margin-top: 50px;
  text-align: center;
  padding: 0 40px;
  page-break-inside: avoid;
}
#terms > span {
  margin-top: 10px;
  font-weight: bold;
  font-size: 24px;
  display: block;
}
#terms > div {
  min-height: 50px;
  line-height: 1.8em;
}

.cc {
  text-align: center;
  padding-bottom: 90px;
  margin-top: 20px;
}

.bottom-stripe {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 50px;
  background: #609BCB;
}

.ib_bottom_row_commands {
  margin-top: 50px !important;
  margin-left: 40px;
}

[data-row-number] {
  display: none;
}

.ib_invoicebus_fineprint {
  color: white !important;
}

/**
 * If the printed invoice is not looking as expected you may tune up
 * the print styles (you can use !important to override styles)
 */
@media print {
  /* Here goes your print styles */
}

  </style>
</head>
 <body>
    <div id="container">
      <div class="invoice-top">
        <section id="memo">

          <section id="invoice-title-number">
            <span id="title">{invoice_title}</span>
            <br>
            <span class="company-name">{company_name}</span>
            <span id="number">{invoice_number}</span>
          </section>
          
          <section id="invoice-info">
            <div>
              <span>{issue_date_label}</span> <span>{issue_date}</span>
            </div>
            <div>
              <span>{net_term_label}</span> <span>{net_term}</span>
            </div>
            <div>
              <span>{due_date_label}</span> <span>{due_date}</span>
            </div>
            <div>
              <span>{po_number_label}</span> <span>{po_number}</span>
            </div>
          </section>

        </section>

        <div class="clearfix"></div>

        <div class="company-info">
          <table class="company-table" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td>
                <table cellpadding="0" cellspacing="0" border="0">
                  <tr>
                    <td class="icon">
                      <div class="icon-mail"></div>
                    </td>
                    <td class="info large">
                      <div>{company_address}</div>
                      <div>{company_city_zip_state}</div>
                    </td>
                  </tr>
                </table>
              </td>

              <td class="second">
                <table cellpadding="0" cellspacing="0" border="0">
                  <tr>
                    <td class="icon">
                      <div class="icon-globe"></div>
                    </td>
                    <td class="info large">
                      <div>{company_email_web}</div>
                    </td>
                  </tr>
                </table>
              </td>

              <td>
                <table cellpadding="0" cellspacing="0" border="0">
                  <tr>
                    <td class="icon">
                      <div class="icon-phone"></div>
                    </td>
                    <td class="info small">
                      <div>{company_phone_fax}</div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </div>

        <div class="logo">
          <img data-logo="{company_logo}" />
        </div>

        <div class="right-side">

          <div class="payment-info">
            <div class="bold">{payment_info1}</div>
            <div>{payment_info2}</div>
            <div>{payment_info3}</div>
            <div>{payment_info4}</div>
            <div>{payment_info5}</div>
          </div>
          
          <section id="client-info">
            <span>{bill_to_label}</span>
            <div>
              <span class="bold">{client_name}</span>
            </div>
            
            <div>
              <span>{client_address}</span>
            </div>
            
            <div>
              <span>{client_city_zip_state}</span>
            </div>
            
            <div>
              <span>{client_phone_fax}</span>
            </div>
            
            <div>
              <span>{client_email}</span>
            </div>
            
            <div>
              <span>{client_other}</span>
            </div>
          </section>
        </div>

        <div class="clearfix"></div>

        <div class="spacer"></div>
      </div>

      <div class="invoice-bottom">
        <section id="items">
          
          <table cellpadding="0" cellspacing="0">
          
            <tr>
              <th class="last">{item_row_number_label}</th> <!-- Dummy cell for the row number and row commands -->
              <th class="circle"></th>
              <th>{item_description_label}</th>
              <th>{item_quantity_label}</th>
              <th>{item_price_label}</th>
              <th>{item_discount_label}</th>
              <th>{item_tax_label}</th>
              <th>{item_line_total_label}</th>
              <th class="last"></th>
            </tr>
            
            <tr data-iterate="item">
              <td class="last">{item_row_number}</td> <!-- Don't remove this column as it's needed for the row commands -->
              <td class="circle"><div></div></td>
              <td>{item_description}</td>
              <td>{item_quantity}</td>
              <td>{item_price}</td>
              <td>{item_discount}</td>
              <td>{item_tax}</td>
              <td>{item_line_total}</td>
              <td class="last"></td>
            </tr>
            
          </table>
          
        </section>

        <div class="currency">
          <span class="label">{currency_label}</span> <span class="value">{currency}</span>
        </div>
        
        <section id="sums">
        
          <table cellpadding="0" cellspacing="0">
            <tr>
              <th>{amount_subtotal_label}</th>
              <td>{amount_subtotal}</td>
              <td></td>
            </tr>
            
            <tr data-iterate="tax">
              <th>{tax_name}</th>
              <td>{tax_value}</td>
              <td></td>
            </tr>
            
            <tr class="amount-total">
              <th>{amount_total_label}</th>
              <td>{amount_total}</td>
              <td></td>
            </tr>
            
            <!-- You can use attribute data-hide-on-quote="true" to hide specific information on quotes.
                 For example Invoicebus doesn't need amount paid and amount due on quotes  -->
            <tr data-hide-on-quote="true">
              <th>{amount_paid_label}</th>
              <td>{amount_paid}</td>
              <td></td>
            </tr>
            
            <tr data-hide-on-quote="true">
              <th>{amount_due_label}</th>
              <td>{amount_due}</td>
              <td></td>
            </tr>
            
          </table>
          
        </section>
        
        <div class="clearfix"></div>
        
        <section id="terms">
          
          <div>{terms}</div>
          <span>{terms_label}</span>
          
        </section>

        <div class="cc">
          <img src="./img/cc.png" width="254" height="31">
        </div>

        <div class="bottom-stripe"></div>
      </div>
    </div>

    <script src="http://cdn.invoicebus.com/generator/generator.min.js?data=true"></script>
  </body>
  </html>