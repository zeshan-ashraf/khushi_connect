@extends('layouts.master')
@section('title','Refund Policy')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'Refund Policy' , 'description' => '' , 'bread' => 'Refund Policy' ])

<section class="about-area about-p pt-120 pb-120 p-relative fix">
    <div class="container">
        <div class="row justify-content-center align-items-center">
        <div class="col-md-12">
                  <h3>Overview</h3>
                  <p>Khushipay is committed to providing reliable and efficient payment solutions. However, if you encounter any issues with our services, we offer a refund policy under certain conditions, as outlined below.</p>

                  <h4>Refund Eligibility</h4>
                  <p>Our refund policy applies only to payments made for services provided by Khushipay. If you believe a transaction was made in error or without your authorization, please contact us at <a href="mailto:info@khushipay.com">info@khushipay.com</a> within 30 days of the transaction date to request a review.</p>
                  <p>Refunds are processed under the following conditions:</p>
                  <ul>
                      <li>A duplicate payment was made for the same service.</li>
                      <li>The service was not delivered as described or was unavailable due to technical issues.</li>
                      <li>An unauthorized transaction occurred (subject to verification).</li>
                  </ul>

                  <h4>Non-Refundable Items</h4>
                  <p>We cannot process refunds for the following:</p>
                  <ul>
                      <li>Fees for successfully completed transactions.</li>
                      <li>Payments made through third-party payment gateways unless the issue is directly related to Khushipay’s services.</li>
                      <li>Any charges associated with optional features or upgrades.</li>
                  </ul>

                  <h4>Refund Process</h4>
                  <p>Once your refund request is approved, the refund will be processed, and a credit will automatically be applied to your original payment method within 10 business days. We will notify you via email once the refund has been initiated.</p>

                  <h4>Late or Missing Refunds</h4>
                  <p>If you haven’t received a refund after the specified timeframe, please:</p>
                  <ol>
                      <li>Check your bank account or payment method again.</li>
                      <li>Contact your payment provider or financial institution, as there may be a delay in processing the refund.</li>
                  </ol>
                  <p>If the issue persists, contact us at <a href="mailto:info@khushipay.com">info@khushipay.com</a>.</p>

                  <h4>Disputed Transactions</h4>
                  <p>For disputes regarding payments made through our platform, we encourage you to contact us directly before initiating a chargeback with your bank or payment provider. Resolving disputes through Khushipay is often faster and ensures a smoother process.</p>

                  <h4>Need Help?</h4>
                  <p>If you have questions or need assistance regarding refunds, please contact us at <a href="mailto:info@khushipay.com">info@khushipay.com</a>.</p>
              </div>
        </div>
    </div>
</section>
@endsection
