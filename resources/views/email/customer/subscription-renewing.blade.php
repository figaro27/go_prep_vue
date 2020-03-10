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
@php
$currency = $subscription->store->settings->currency_symbol
@endphp
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
                        <td height="50"></td>
                      </tr>
                      <!-- title -->
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:28px; color:#3b3b3b; line-height:26px;">Subscription Renewal</td>
                      </tr>
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
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:16px; color:#3b3b3b; line-height:26px; font-weight: bold;">{{ $subscription->store_name }}</td>
                      </tr>
                      @if ($subscription->user->details->companyname)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#3b3b3b; line-height:26px;">{{ $subscription->user->details->companyname }}</td>
                      </tr>
                      @endif
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">{{ $subscription->user->details->full_name }}</td>
                      </tr>
                      <!-- end company name -->
                      <!-- address -->
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> {{ $subscription->user->details->address }}
                          <br /> {{ $subscription->user->details->city }}, {{ $subscription->user->details->state }} {{ $subscription->user->details->zip }}
                          </td>
                      </tr>
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> 
                          {{ $subscription->user->details->phone }}
                        </td>
                      </tr>
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> {{ $subscription->interval_title }} Subscription
                        </td>
                      </tr>
                      @if ($subscription->store->modules->hideTransferOptions === 0)
                      @if (!$subscription->latest_order || $subscription->latest_order->pickup === 0)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> Delivery Date - {{ $subscription->next_delivery_date->format($subscription->store->settings->date_format) }}</td>
                      </tr>
                      @else ($subscription->latest_order->pickup === 1)
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> Pickup Date - {{ $subscription->next_delivery_date->format($subscription->store->settings->date_format) }}</td>
                      </tr>
                      @endif
                      @endif
                      <!-- end address -->
                      <tr>
                        <td align="right" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px; ">Subscription #{{ $subscription->stripe_id }}</td>
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
  <!-- end header -->



  <table class="full" align="center" width="100%" bgcolor="#FFFFFF" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="600" align="center">
              <table align="center" width="100%" class="table-inner" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td height="20"></td>
                </tr>
                <!-- title -->
                <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:16px; color:#3b3b3b; line-height:26px;  font-weight: bold; text-transform:uppercase"> 
                  	Hello {{ $subscription->user->details->full_name }},
                  </td>
                </tr>
                <!-- end title -->
                <tr>
                  <td height="15"></td>
                </tr>
                <!-- content -->
                <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> You have a subscription renewing from {{ $subscription->store->details->name }}.
                    @if ($subscription->updated)
                    Your subscription was updated. Please check the meals below.
                    @endif
                    @if (!$subscription->monthlyPrepay)
                    You have 24 hours left to update, or cancel your subscription before orders are locked in and your card is charged.
                    @else
                    You have 24 hours left to update, or cancel your subscription before orders are locked in.
                    @endif
                  </td>
                </tr>
                <tr>
                  <td height="15"></td>
                </tr>
                <tr>
                	<td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;">Click the button below if you would like to update your subscription.</td>
                </tr>
                <tr>
                  <td height="30"></td>
                </tr>

                <tr>
                <td><a href="http://goprep.com/customer/subscriptions"><button bgcolor="#E04F00" align="center" style="font-family:'Open Sans'; font-size:16px; color:#ffffff;padding-left:25px;padding-right:25px;border-radius:5px;background-color:#F25727;width:100%;height:55px;border:none">Go</button></a></td>
                </tr>

                <tr>
                  <td height="30"></td>
                </tr>
                
                <!-- end content -->
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
          @if ($subscription->pickup_location_id != null)
          <tr>
            <td height="50" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#7f8c8d;"> <b>Pickup Location:</b>
                  {{ $subscription->pickup_location->name }}, 
                  {{ $subscription->pickup_location->address }},
                  {{ $subscription->pickup_location->city }},
                  {{ $subscription->pickup_location->state }},
                  {{ $subscription->pickup_location->zip }}<br><br>
                  @if ($subscription->pickup_location->instructions)
                  <b>Instructions:</b> {{ $subscription->pickup_location->instructions }}
                  @endif
                  </td>
          </tr>
          @endif
          <!-- header -->
          <tr>
            <td height="15"></td>
          </tr>
          <tr>
            <td>
              <table class="table-inner" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="263" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#3b3b3b; line-height:26px; text-transform:uppercase;">Item Name</td>
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
            <td height="15"></td>
          </tr>
          <tr>
            <td align="center">
              <table width="100%" class="table-inner" border="0" cellspacing="0" cellpadding="0">

                @foreach($subscription->meal_package_items as $mealPackageItem)

                <tr>
                  <td width="263" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px;font-weight: bold; ">{{ $mealPackageItem->meal_package->title }} 
                    @if ($mealPackageItem->meal_package_size)
                    - {{ $mealPackageItem->meal_package_size->title }}
                    @endif
                  </td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px;font-weight: bold; ">
                    {{$currency}}{{ number_format($mealPackageItem->price, 2) }}
                  </td>
                  <td width="87" align="center" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px;font-weight: bold; ">
                    {{ $mealPackageItem->quantity }}
                  </td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px;  font-weight: bold;">
                    {{$currency}}{{ number_format($mealPackageItem->price * $mealPackageItem->quantity, 2) }}
                  </td>
                </tr>


                @foreach($subscription->items as $item)
                @if ($item->meal_package_subscription_id === $mealPackageItem->id)
                <tr>
                  <td width="263" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">{!! $item->html_title !!}</td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">
                    @if ($item->meal_package_subscription_id === null)
                    {{$currency}}{{ number_format($item->unit_price, 2) }}
                    @else
                    In Package
                    @endif
                    </td>
                  <td width="87" align="center" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">{{ $item->quantity }}</td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px;">
                    @if ($item->meal_package_subscription_id === null)
                    {{$currency}}{{ number_format($item->price, 2) }}
                    @else
                    In Package
                    @endif
                  </td>
                </tr>
                @endif
                @endforeach

              @endforeach
                
                @foreach($subscription->items as $item)
                @if ($item->meal_package_subscription_id === null)
                <tr>
                  <td width="263" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">{!! $item->html_title !!}</td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">
                    {{$currency}}{{ number_format($item->unit_price, 2) }}
                    </td>
                  <td width="87" align="center" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px; ">{{ $item->quantity }}</td>
                  <td width="87" align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b; line-height:26px;">
                    {{$currency}}{{ number_format($item->price, 2) }}
                  </td>
                </tr>
                @endif
              @endforeach
              </table>
            </td>
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
            <td height="20" style="border-bottom:3px solid #bcbcbc;"></td>
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
                        $subtotal = $subscription->preFeePreDiscount;
                        $mealPlanDiscount = $subscription->mealPlanDiscount;
                        $deliveryFee = $subscription->deliveryFee;
                        $processingFee = $subscription->processingFee;
                        $salesTax = $subscription->salesTax;
                        $couponCode = $subscription->couponCode;
                        $couponReduction = $subscription->couponReduction;
                        @endphp

                        Subtotal: <br>
                        @if ($couponCode)
                        Coupon ({{ $couponCode }})<br>
                        @endif
                        @if ($mealPlanDiscount > 0)
                        Subscription Discount<br>
                        @endif
                        @if ($salesTax > 0)
                        Sales Tax<br>
                        @endif
                        @if ($deliveryFee > 0)
                        Delivery Fee<br>
                        @endif
                        @if ($processingFee > 0)
                        Processing Fee<br>
                        @endif
                        
                        <br>
                        <span style="font-family: 'Open Sans', Arial, sans-serif; font-size:24px; color:#3b3b3b;  font-weight: bold;">Total</span>
                      </td>

                    
                      
                        <td bgcolor="#e1e6e7" style="padding-left:15px;font-family: 'Open Sans', Arial, sans-serif; font-size:12px; color:#3b3b3b; line-height:26px; text-transform:uppercase;line-height:24px;">
                          {{$currency}}{{ number_format($subtotal, 2) }}<br>
                          @if ($couponReduction > 0)
                          ({{$currency}}{{ number_format($couponReduction, 2) }})<br>
                          @endif
                          @if ($mealPlanDiscount > 0)
                          ({{$currency}}{{ number_format($mealPlanDiscount, 2) }})<br>
                          @endif
                          @if ($salesTax > 0)
                          {{$currency}}{{ number_format($salesTax, 2) }}<br>
                          @endif
                          @if ($deliveryFee > 0)
                          {{$currency}}{{ number_format($deliveryFee, 2) }}<br>
                          @endif
                          @if ($processingFee > 0)
                          {{$currency}}{{ number_format($processingFee, 2) }}<br>
                          @endif
                          
                          <br>
                          <span style="font-family: 'Open Sans', Arial, sans-serif; font-size:24px; color:#3b3b3b;  font-weight: bold;">{{$currency}}{{ number_format($subscription->amount, 2) }}</span>
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
                  <td height="20"></td>
                </tr>
                <!-- title -->
                @if ($subscription->store->settings->deliveryInstructions)
                <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:16px; color:#3b3b3b; line-height:26px;  font-weight: bold; text-transform:uppercase">Delivery Instructions</td>
                </tr>
                <!-- end title -->
                <tr>
                  <td height="5"></td>
                </tr>
                <!-- content -->
                @if (!$subscription->latest_order || $subscription->latest_order->pickup === 0)
				        <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;"> {{ $subscription->user->details->delivery }} </td>
                </tr>
                @endif
                @if ($subscription->latest_order && $subscription->latest_order->pickup === 1)
                <tr>
                  <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d; line-height:26px;">Pickup </td>
                </tr>
				        @endif
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





