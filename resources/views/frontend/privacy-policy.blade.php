@extends('layouts.master')
@section('title','Privacy Policy')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'Privacy Policy' , 'description' => '' , 'bread' => 'Privacy Policy' ])

<section class="about-area about-p pt-120 pb-120 p-relative fix">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-12">
                <p>Welcome to <strong>Khushipay</strong> <a href="https://khushipay.com/"
                        target="_blank">(https://khushipay.com/)</a>. Your privacy is of utmost importance to us, and we
                    are committed to protecting the personal information you share with us. This Privacy Policy outlines
                    how we collect, use, and safeguard your information.</p>

                <h4>1. Information We Collect</h4>
                <ul>
                    <li><strong>Personal Information:</strong> Name, email address, phone number, and other contact
                        details. Billing and financial information for transactions.</li>
                    <li><strong>Non-Personal Information:</strong> Browser type, device information, and IP address.
                        Website usage data, such as pages visited and time spent on our site.</li>
                    <li><strong>Payment Information:</strong> We collect payment details to process transactions
                        securely.</li>
                </ul>

                <h4>2. How We Use Your Information</h4>
                <p>We use your information to provide and improve our services, process payments, communicate with you,
                    and comply with legal requirements.</p>

                <h4>3. Information Sharing and Disclosure</h4>
                <p>We do not sell or rent your personal information. However, we may share information with third
                    parties for the following reasons:</p>
                <ul>
                    <li>To process payments via trusted payment gateways.</li>
                    <li>To comply with legal obligations.</li>
                    <li>To protect the rights, property, or safety of Khushipay, its users, or others.</li>
                </ul>

                <h4>4. Data Security</h4>
                <p>We use appropriate security measures to protect your personal information. However, no online system
                    can guarantee absolute security.</p>

                <h4>5. Your Rights</h4>
                <p>You have the right to access, update, or delete your personal information. Please contact us at <a
                        href="mailto:info@khushipay.com">info@khushipay.com</a> for assistance.</p>

                <h4>6. Changes to This Policy</h4>
                <p>We may update this Privacy Policy from time to time. Any changes will be reflected on this page with
                    an updated 10/11/2024</p>

                <h4>7. Contact Us</h4>
                <p>If you have any questions or concerns about this Privacy Policy, please contact us at <a
                        href="mailto:info@khushipay.com">info@khushipay.com</a>.</p>
            </div>
        </div>
    </div>
</section>
@endsection
