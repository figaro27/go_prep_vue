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
  <table class="full" bgcolor="#f8f8f8" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center">
        <table width="600" style="max-width: 600px" class="table-full" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td align="center">
              <table width="200" class="table-full" align="left" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td bgcolor="#3082CF" align="center">
                    <table width="80%" class="table-inner" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="50"></td>
                      </tr>
                      <!-- logo -->
                      <tr>
                        <td align="center" style="line-height:0px;"><img style="display:block;font-size:0px; border:0px; line-height:0px;" src="https://goprep.com/logo.png" alt="GoPrep" title="GoPrep"/></td>
                      </tr>
                      <!-- end logo -->
                      <tr>
                        <td height="40"></td>
                      </tr>
                      <!-- company name -->
                      <!-- end company name -->
                      <tr>
                        <td height="5"></td>
                      </tr>
                      <!-- address -->
                      <tr>
                        <td style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#FFFFFF; line-height:26px;text-align: center;" align="center"> Meal Prep, Simplified.
                           </td>
                      </tr>
                      <!-- end address -->
                      <tr>
                        <td height="100"></td>
                      </tr>
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
                        <td height="50"></td>
                      </tr>
                      <!-- title -->
                      @if ($order->pickup === 0)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:38px; color:#3b3b3b; line-height:26px;">Delivery Today</td>
                      </tr>
                      @elseif ($order->pickup === 1)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:38px; color:#3b3b3b; line-height:26px;">Pickup Today</td>
                      </tr>
                      @endif
                      <!-- end title -->
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
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:16px; color:#3b3b3b; line-height:26px; font-weight: bold;">{{ $order->store_name }}</td>
                      </tr>
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">{{ $customer->name }}</td>
                      </tr>
                      <!-- end company name -->
                      <!-- address -->
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> {{ $customer->address }}</td>
                      </tr>
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;">
                          {{ $customer->city }}, {{ $customer->state }} {{ $customer->zip }}
                          </td>
                      </tr>
                      @if ($order->pickup === 0)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> Delivery Date - {{ $order->delivery_date->format('D, m/d/Y') }}</td>
                      </tr>
                      @else ($order->pickup === 1)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> Pickup Date - {{ $order->delivery_date->format('D, m/d/Y') }}</td>
                      </tr>
                      @endif
                      <!-- end address -->
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">Order #{{ $order->order_number }}</td>
                      </tr>
                      @if ($order->subscription)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">Meal Plan #{{ $order->subscription->stripe_id }}</td>
                      </tr>
                      @endif
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
  <!-- end header -->


   <table class="full" align="center" width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="600" align="center">
              <table align="center" width="100%" class="table-inner" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td height="40"></td>
                </tr>
                <!-- title -->
                <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:16px; color:#3b3b3b; line-height:26px;  font-weight: bold; text-transform:uppercase"> 
                  	Hello {{ $customer->name }},
                  </td>
                </tr>
                <!-- end title -->
                <tr>
                  <td height="5"></td>
                </tr>
                <!-- content -->
                @if ($order->pickup === 0)
				<tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> You have a delivery scheduled for today from {{ $order->store_name }} </td>
                </tr>
                @else
                <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> You have a pickup scheduled for today from {{ $order->store_name }} </td>
                </tr>
                @if ($order->pickup_location_id != null)
                  <tr>
                    <td height="50" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d;"> Pickup Location:
                    {{ $order->pickup_location->name }}<br>
                    {{ $order->pickup_location->address }},
                    {{ $order->pickup_location->city }},
                    {{ $order->pickup_location->state }},
                    {{ $order->pickup_location->zip }}
                    </td>
                  </tr>
                @endif
				@endif
                
                <!-- end content -->
                <tr>
                  <td height="15" style="border-bottom:3px solid #bcbcbc;"></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>





  <!-- title -->
  <table class="full" width="100%" align="center" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" width="600" style="max-width:600px;" class="table-full" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td height="50"></td>
          </tr>
          <!-- header -->
          <tr>
            <td>
              <table class="table-inner" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="263" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#3b3b3b; line-height:26px; text-transform:uppercase;">Meal Name</td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#3b3b3b; line-height:26px; text-transform:uppercase;">Price</td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#3b3b3b; line-height:26px; text-transform:uppercase;">Quantity</td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#3b3b3b; line-height:26px; text-transform:uppercase;">Total</td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- end header -->
          <tr>
            <td height="10" style="border-bottom:3px solid #bcbcbc;"></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!-- end title -->
  <!-- list -->
  <table class="full" align="center" width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table width="600" style="max-width: 600px;" class="table-full" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td height="35"></td>
          </tr>
          <tr>
            <td align="center">
              <table width="100%" class="table-inner" border="0" cellspacing="0" cellpadding="0">
              	@php
              		$lineItemTotal = 0;
              	@endphp
              	@foreach($order->meals as $meal)
              		@php
		            	$lineItemTotal += $meal->item_price * $meal->item_quantity;
		            @endphp
              	<tr>
	              	<td width="263" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">{{ $meal->item_title }}</td>
	              	<td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">
	              		${{ number_format($meal->item_price, 2) }}</td>
	              	<td width="87" align="center" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">{{ $meal->item_quantity }}</td>
	              	<td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px;  font-weight: bold;">${{ number_format($lineItemTotal, 2) }}</td>
              	</tr>
              		@php
		            	$lineItemTotal = 0;
		            @endphp
				@endforeach
              </table>
            </td>
          </tr>
          <tr>
            <td height="5" style="border-bottom:1px solid #ecf0f1;"></td>
          </tr>
          <tr>
            <td height="5"></td>
          </tr>
          <!-- detail -->
          <tr>
          </tr>
          <!-- end detail -->
        </table>
      </td>
    </tr>
  </table>
  <!-- end list -->
  <!-- total -->
  <table class="full" align="center" width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table width="600" class="table-full" style="max-width: 600px;" align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td height="40" style="border-bottom:3px solid #3b3b3b;"></td>
          </tr>
        </table>
        <table align="center" width="600" style="max-width: 600px;" class="table-full" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td height="15"></td>
          </tr>
          <tr>
            <td align="center">
              <table width="100%" class="table-full" align="left" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td bgcolor="#f8f8f8" align="center">
                    <table class="table-inner" align="center" width="100%" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="10"></td><td bgcolor="#e1e6e7"></td>
                      </tr>
                      <tr>
                        <td style="padding-left:15px;font-family: 'Open Sans', Arial, sans-serif; font-size:12px; color:#3b3b3b; line-height:26px; text-transform:uppercase;line-height:24px;">@php
                        $subtotal = $order->preFeePreDiscount;
                        $mealPlanDiscount = $order->mealPlanDiscount;
                        $deliveryFee = $order->deliveryFee;
                        $processingFee = $order->processingFee;
                        $salesTax = $order->salesTax;
                        $coupon = $order->couponReduction;
                        $couponCode = $order->couponCode;
                        @endphp

                        Subtotal: <br>
                        @if ($coupon > 0)
                        Coupon ({{ $couponCode }})<br>
                        @endif
                        @if ($mealPlanDiscount > 0)
                        Meal Plan Discount<br>
                        @endif
                        @if ($deliveryFee > 0)
                        Delivery Fee<br>
                        @endif
                        @if ($processingFee > 0)
                        Processing Fee<br>
                        @endif
                        Sales Tax<br><br>
                        <span style="font-family: 'Open Sans', Arial, sans-serif; font-size:24px; color:#3b3b3b;  font-weight: bold;">Total</span>
                      </td>

                    
                      
                        <td bgcolor="#e1e6e7" style="padding-left:15px;font-family: 'Open Sans', Arial, sans-serif; font-size:12px; color:#3b3b3b; line-height:26px; text-transform:uppercase;line-height:24px;">
                          ${{ number_format($subtotal, 2) }}<br>
                          @if ($coupon > 0)
                          (${{ number_format($coupon, 2) }})<br>
                          @if ($mealPlanDiscount > 0)
                          (${{ number_format($mealPlanDiscount, 2) }})<br>
                          @endif
                          @if ($deliveryFee > 0)
                          ${{ number_format($deliveryFee, 2) }}<br>
                          @endif
                          @if ($processingFee > 0)
                          ${{ number_format($processingFee, 2) }}<br>
                          @endif
                          ${{ number_format($salesTax, 2) }}<br>
                          @endif<br><br>
                          <span style="font-family: 'Open Sans', Arial, sans-serif; font-size:24px; color:#3b3b3b;  font-weight: bold;">${{ number_format($order->amount, 2) }}</span>
                        </td>
                      </tr>
                  </td>
                      </tr>
                      
                      <tr>
                        <td height="15"></td><td bgcolor="#e1e6e7"></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

            </td>
          </tr>
          <tr>
            <td height="15"></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!-- end total -->
  <!-- note -->
  <table class="full" align="center" width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="600" align="center">
              <table align="center" width="100%" class="table-inner" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td height="40"></td>
                </tr>
                <!-- title -->
               <tr>
                  @if ($order->pickup === 0)
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:16px; color:#3b3b3b; line-height:26px;  font-weight: bold; text-transform:uppercase">Delivery Instructions</td>
                  @else
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:16px; color:#3b3b3b; line-height:26px;  font-weight: bold; text-transform:uppercase">Pickup Instructions</td>
                  @endif
                </tr>
                <!-- end title -->
                <tr>
                  <td height="5"></td>
                </tr>
                <!-- content -->
                @if ($order->pickup === 0)
        <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> 
                    {!! nl2br($order->store->settings->deliveryInstructions) !!} 
                  </td>
                </tr>
                @else
                <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> 
                    {!! nl2br($order->store->settings->pickupInstructions !!} 
                  </td>
                </tr>
                @endif
                
                <!-- end content -->
                <tr>
                  <td height="15" style="border-bottom:3px solid #bcbcbc;"></td>
                </tr>
                <tr>
                  <td height="45" style="text-align: center;"><a href="https://goprep.com/customer/account/my-account">Unsubscribe</a></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!-- end note -->
  <!-- footer -->
  <!-- end footer -->
</body>

</html>





