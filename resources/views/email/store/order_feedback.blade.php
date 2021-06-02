                        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <!--[if !mso]><!-->
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!--<![endif]-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title></title>
  <style type="text/css">
.ReadMsgBody { width: 100%; background-color: #ffffff; }
.ExternalClass { width: 100%; background-color: #ffffff; }
.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }
html { width: 100%; }
body { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; margin: 0; padding: 0; font-family: 'Open Sans', Arial, Sans-serif !important; }
table { border-spacing: 0; table-layout: auto; margin: 0 auto; }
img { display: block !important; overflow: hidden !important; }
.yshortcuts a { border-bottom: none !important; }
img:hover { opacity: 0.9 !important; }
a { color: #4a4a4a; text-decoration: none; }
.textbutton a { font-family: 'open sans', arial, sans-serif !important;}
.btn-link a { color:#FFFFFF !important;}

/*Responsive*/
@media only screen and (max-width: 640px) {
body { margin: 0px; width: auto !important; font-family: 'Open Sans', Arial, Sans-serif !important;}
.table-inner { width: 90% !important;  max-width: 90%!important;}
.table-full { width: 100%!important; max-width: 100%!important; text-align: center !important;}
}

@media only screen and (max-width: 479px) {
body { width: auto !important; font-family: 'Open Sans', Arial, Sans-serif !important;}
.table-inner{ width: 90% !important; text-align: center !important;}
.table-full { width: 100%!important; max-width: 100%!important; text-align: center !important;}
/*gmail*/
u + .body .full { width:100% !important; width:100vw !important;}
}
</style>
</head>

<body class="body">
  <!-- header -->
  <table class="full" bgcolor="#ffffff" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center">
        <table width="600" style="max-width: 600px" class="table-full" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td align="center">
              <table width="200" class="table-full" align="left" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td bgcolor={{ (isset($logo_b64) && $logo_b64 != "")?"transparent":"#3082CF" }} align="center">
                    <table width="80%" class="table-inner" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="80"></td>
                      </tr>
                      <!-- logo -->
                      <tr>
                        @if (isset($logo_b64) && $logo_b64 != "")
                        <td align="center" style="line-height:0px;"><img style="display:block;font-size:0px; border:0px; line-height:0px;" src="{{$logo_b64}}" alt="GoPrep" title="GoPrep" /></td>
                        @else
                        <td align="center" style="line-height:0px;"><img style="display:block;font-size:0px; border:0px; line-height:0px;" src="https://goprep.com/logo.png" alt="GoPrep" title="GoPrep" /></td>
                        @endif
                      </tr>
                      <!-- end logo -->
                      <!-- address -->
                      @if (!isset($logo_b64) || $logo_b64 == "")
                      <tr>
                        <td style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#FFFFFF; line-height:26px;text-align: center;" align="center"> Meal Prep, Simplified.
                           </td>
                      </tr>
                      <tr>
                        <td height="120"></td>
                      </tr>
                      @endif
                      <!-- end address -->
                    </table>
                  </td>
                </tr>
              </table>
              <!--[if (gte mso 9)|(IE)]></td><td><![endif]-->
              <table width="400" class="table-full" border="0" align="right" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center">
                    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="25"></td>
                      </tr>
                      <!-- title -->
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:28px; color:#3b3b3b; line-height:26px;">Order Feedback</td>
                      </tr>
                      <tr>
                        <td height="5"></td>
                      </tr>
                      <!-- end title -->
                      @if ($order->customer_company && $order->customer_company !== 'N/A')
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#3b3b3b; line-height:26px;">{{ $order->customer_company }}</td>
                      </tr>
                      @endif
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">{{ $order->customer_name }}</td>
                      </tr>
                      <!-- end company name -->
                      <!-- address -->
                      @if ($order->customer_address !== 'N/A')
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> {{ $order->customer_address }}</td>
                      </tr>
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;">
                          {{ $order->customer_city }}, {{ $order->customer_state }} {{ $order->customer_zip }}
                          </td>
                      </tr>
                      @endif
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> 
                          {{ $order->customer_phone }}
                        </td>
                      </tr>
                      @if ($order->store->modules->hideTransferOptions === 0 && $order->isMultipleDelivery === 0)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> {{ $order->transfer_type }} Date - {{ $order->delivery_date->format($order->store->settings->date_format) }}
                          @if ($order->transferTime)
                            - {{ $order->transferTime }}
                          @endif
                        </td>
                      </tr>
                      @endif
                      @if ($order->isMultipleDelivery === 1)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> Delivery Dates - {{ $order->multipleDates }}
                        </td>
                      </tr>
                      @endif
                      <!-- end address -->
                      @if ($order->store->modules->dailyOrderNumbers)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">Daily Order #{{ $order->dailyOrderNumber }}</td>
                      </tr>
                      @endif
                      <tr>
                        @if ($order->manual)
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">Manual Order ID {{ $order->order_number }}</td>
                        @else
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">Order ID {{ $order->order_number }}</td>
                        @endif
                      </tr>
                      <tr>
                        @if ($order->staff_id)
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">Order Taken By: {{ $order->staff_member }}</td>
                        @endif
                      </tr>
                      @if ($order->subscription)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">Subscription #{{ $order->subscription->stripe_id }}</td>
                      </tr>
                      @endif

                      
                      <tr>
                        <td height="25"></td>
                      </tr>
                      <tr>
                        <td align="right">
                          <table align="right" width="50" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                              <td bgcolor="#E85A00" height="3" style="line-height:0px; font-size:0px;">&nbsp;</td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td height="15"></td>
                      </tr>
                      <!-- company name -->
                      
                      <tr>
                        <td height="25">

                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <table class="full" width="100%" align="center" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" width="600" style="max-width:600px;" class="table-full" border="0" cellspacing="0" cellpadding="0">
          <!-- header -->
          <tr>
            <td>
              <table class="table-inner" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="263" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#3b3b3b; line-height:26px;">
                  @foreach ($surveyResponses as $response)
                  <div style="margin-top:20px;margin-bottom:20px">
                    <div style="font-weight:bold;font-size:20px;">{{ $response->survey_question }}</div>
                    <div>{{ $response->response }}</div>
                  </div>
                  @endforeach

                  @php
                  $items = [];
                  @endphp

                  @foreach ($surveyItemResponses as $i => $response)
                  <div style="margin-top:20px;margin-bottom:20px">
                  @if (!in_array($response->item_name, $items))
                  <div style="font-weight:bold;font-size:20px;">{!! $response->item_name !!}</div>
                  @endif
                  <div style="font-weight:bold">{{ $response->survey_question }}</div>
                  <div style="">{{ $response->response }}</div>
                  @php
                    array_push($items, $response->item_name)
                  @endphp
                  </div>
                  @endforeach
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- end header -->
          <tr>
            
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>

</html>


