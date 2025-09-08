@php
  // Provided logo
  $logo = $logo_url ?? 'https://gorealestate.b-cdn.net/Gallery/clrlogo.png';
@endphp
<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="utf-8">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ $title ?? 'Notification' }}</title>
  <style>
    body,table,td,a{ -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;}
    table,td{ mso-table-lspace:0pt; mso-table-rspace:0pt;}
    img{ -ms-interpolation-mode:bicubic; border:0; outline:none; text-decoration:none; display:block;}
    body{ margin:0; padding:0; width:100%!important; height:100%!important; background:#f5f7fb;}
    .wrapper{ width:100%; background:#f5f7fb; padding:24px 12px;}
    .container{ max-width:600px; margin:0 auto;}
    .card{ background:#ffffff; border-radius:8px; box-shadow:0 1px 2px rgba(0,0,0,0.05);}
    .px{ padding-left:24px; padding-right:24px;}
    .p{ padding:24px;}
    .pt{ padding-top:24px;}
    .pb{ padding-bottom:24px;}
    .center{ text-align:center;}
    .h1{ font-family:Arial,'Helvetica Neue',Helvetica,sans-serif; font-size:22px; line-height:28px; font-weight:700; color:#111827; margin:0;}
    .text{ font-family:Arial,'Helvetica Neue',Helvetica,sans-serif; font-size:15px; line-height:22px; color:#374151; margin:0;}
    .muted{ color:#6b7280;}
    .divider{ height:1px; line-height:1px; background:#e5e7eb;}
    .btn{ background:#1a73e8; color:#ffffff; text-decoration:none; display:inline-block; padding:12px 22px; border-radius:6px; font-weight:700; font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;}
    .kv-table{ width:100%; border-collapse:collapse;}
    .kv-table td{ padding:8px 0; font-family:Arial,'Helvetica Neue',Helvetica,sans-serif; font-size:14px;}
    .kv-key{ color:#6b7280; width:38%; vertical-align:top;}
    .kv-val{ color:#111827; width:62%;}
    @media (max-width:600px){ .px{ padding-left:16px!important; padding-right:16px!important;} .p{ padding:16px!important; } }
  </style>
  <!--[if mso]>
  <style>.card{ border-radius:0 !important; }</style>
  <![endif]-->
</head>
<body>
  <!-- Preheader (hidden preview text) -->
  <div style="display:none; overflow:hidden; line-height:1px; opacity:0; max-height:0; max-width:0;">
    {{ $preheader ?? '' }}
  </div>

  <table role="presentation" class="wrapper" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center">
        <table role="presentation" class="container" cellpadding="0" cellspacing="0" width="100%">
          <!-- Header / Logo -->
          <tr>
            <td class="center pb">
              <img src="{{ $logo }}" width="160" alt="GO Real Estate">
            </td>
          </tr>

          <!-- Card -->
          <tr>
            <td class="card p">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="pb">
                    <h1 class="h1">{{ $title ?? 'Notification' }}</h1>
                  </td>
                </tr>
                <tr>
                  <td class="pb">
                    <div class="text">{!! $intro ?? 'Hello! This is a system notification.' !!}</div>
                  </td>
                </tr>

                @if(!empty($details) && is_array($details))
                <tr><td class="pb"><div class="divider"></div></td></tr>
                <tr>
                  <td class="pb">
                    <table role="presentation" class="kv-table">
                      @foreach($details as $k => $v)
                        <tr>
                          <td class="kv-key">{{ $k }}</td>
                          <td class="kv-val">{!! nl2br(e($v)) !!}</td>
                        </tr>
                      @endforeach
                    </table>
                  </td>
                </tr>
                @endif

                @if(!empty($cta_url) && !empty($cta_label))
                <tr>
                  <td class="center pb">
                    <!--[if mso]>
                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $cta_url }}" style="height:44px;v-text-anchor:middle;width:240px;" arcsize="8%" stroke="f" fillcolor="#1a73e8">
                      <w:anchorlock/>
                      <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;">{{ $cta_label }}</center>
                    </v:roundrect>
                    <![endif]-->
                    <!--[if !mso]><!-- -->
                    <a class="btn" href="{{ $cta_url }}" target="_blank" rel="noopener">{{ $cta_label }}</a>
                    <!--<![endif]-->
                    <div class="text muted" style="margin-top:8px;">
                      If the button doesn’t work, copy & paste:<br>
                      <a href="{{ $cta_url }}" style="color:#1a73e8; text-decoration:underline; word-break:break-all;">{{ $cta_url }}</a>
                    </div>
                  </td>
                </tr>
                @endif
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td class="center px pt">
              <p class="text muted" style="font-size:12px;">
                {{ $footer_text ?? '© ' . date('Y') . ' GO Group Invest. All rights reserved.' }}
              </p>
            </td>
          </tr>
          <tr><td style="height:24px;"></td></tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
