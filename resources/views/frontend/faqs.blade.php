@extends('layouts.master')
@section('title','Faqs')
@section('content')

@include('frontend.component.bredcrumb' , ['title'=>'Faqs' , 'description' => '' , 'bread' => 'Faqs' ])

<section id="faq" class="faq-area pt-120 pb-120">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6">
                <div class="section-title center-align mb-50 text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h5 style="font-size: 28px;">General FAQs</h5>
                </div>
                <div class="faq-wrap">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse1" aria-bs-expanded="false"
                                        aria-bs-controls="collapse1">
                                        What is Khushipay?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse1" class="collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Khushipay is a payment gateway provider in Pakistan, facilitating online transactions for businesses and individuals.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse2" aria-bs-expanded="false"
                                        aria-bs-controls="collapse2">
                                        What services does Khushipay offer?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse2" class="collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Khushipay offers payment gateway solutions, online payment processing, and merchant services.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse3" aria-bs-expanded="false"
                                        aria-bs-controls="collapse3">
                                        How secure is Khushipay?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse3" class="collapse" aria-labelledby="headingThree"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Khushipay uses PCI-DSS compliant security measures, encryption, and 2-factor authentication to ensure secure transactions.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-title center-align mb-50 mt-50  text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h5 style="font-size: 28px;">Merchant FAQs</h5>
                </div>
                <div class="faq-wrap">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse4" aria-bs-expanded="false"
                                        aria-bs-controls="collapse4">
                                        How do I become a Khushipay merchant?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse4" class="collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Register on our website, provide required documents, and wait for verification and approval.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse5" aria-bs-expanded="false"
                                        aria-bs-controls="collapse5">
                                        What documents are required for merchant registration?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse5" class="collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                CNIC, business registration documents, and proof of address.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse6" aria-bs-expanded="false"
                                        aria-bs-controls="collapse6">
                                        What is the merchant registration fee?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse6" class="collapse" aria-labelledby="headingThree"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Please contact our sales team for fee details.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-title center-align mb-50 mt-50 text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h5 style="font-size: 28px;">Transaction FAQs</h5>
                </div>
                <div class="faq-wrap">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse7" aria-bs-expanded="false"
                                        aria-bs-controls="collapse7">
                                        What types of payments does Khushipay support?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse7" class="collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Credit/debit cards, mobile wallets, and internet banking.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse8" aria-bs-expanded="false"
                                        aria-bs-controls="collapse8">
                                        What is the transaction processing time?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse8" class="collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Real-time processing for most transactions.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse9" aria-bs-expanded="false"
                                        aria-bs-controls="collapse9">
                                        Can I refund or cancel a transaction?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse9" class="collapse" aria-labelledby="headingThree"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Yes, contact our support team for assistance.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-title center-align mb-50 mt-50 text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h5 style="font-size: 28px;">Technical FAQs</h5>
                </div>
                <div class="faq-wrap">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse10" aria-bs-expanded="false"
                                        aria-bs-controls="collapse10">
                                        What integration options does Khushipay offer?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse10" class="collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                API integration, plugin integration, and hosted payment pages.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse11" aria-bs-expanded="false"
                                        aria-bs-controls="collapse11">
                                        What programming languages do you support?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse11" class="collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Java, PHP, Python, and .NET.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse12" aria-bs-expanded="false"
                                        aria-bs-controls="collapse12">
                                        How do I test my integration?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse12" class="collapse" aria-labelledby="headingThree"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Contact our support team for test credentials and assistance.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="section-title center-align mb-50 text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h5 style="font-size: 28px;">Support FAQs</h5>
                </div>
                <div class="faq-wrap">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse13" aria-bs-expanded="false"
                                        aria-bs-controls="collapse13">
                                        How do I contact Khushipay support?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse13" class="collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Contact through email: support@khushipay.com
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse14" aria-bs-expanded="false"
                                        aria-bs-controls="collapse14">
                                        What are Khushipay's support hours?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse14" class="collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Monday-Friday, 9am-5pm PST.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse15" aria-bs-expanded="false"
                                        aria-bs-controls="collapse15">
                                        Can I request a callback from support?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse15" class="collapse" aria-labelledby="headingThree"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Yes, submit a request on our website.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-title center-align mb-50 mt-50 text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h5 style="font-size: 28px;">Security and Compliance FAQs</h5>
                </div>
                <div class="faq-wrap">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse16" aria-bs-expanded="false"
                                        aria-bs-controls="collapse16">
                                        Is Khushipay PCI-DSS compliant?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse16" class="collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Yes, Khushipay adheres to PCI-DSS standards.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse17" aria-bs-expanded="false"
                                        aria-bs-controls="collapse17">
                                        How does Khushipay protect customer data?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse17" class="collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Encryption, secure servers, and access controls.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse18" aria-bs-expanded="false"
                                        aria-bs-controls="collapse18">
                                        What is Khushipay's policy on fraud prevention?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse18" class="collapse" aria-labelledby="headingThree"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Monitoring, reporting, and cooperation with authorities.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-title center-align mb-50 mt-50 text-center wow fadeInDown  animated"
                    data-animation="fadeInDown" data-delay=".4s"
                    style="visibility: visible; animation-name: fadeInDown;">
                    <h5 style="font-size: 28px;">Fee and Pricing FAQs
                    </h5>
                </div>
                <div class="faq-wrap">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse19" aria-bs-expanded="false"
                                        aria-bs-controls="collapse19">
                                        What are Khushipay's transaction fees?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse19" class="collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Please contact our sales team for fee details.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse20" aria-bs-expanded="false"
                                        aria-bs-controls="collapse20">
                                        Are there any setup or monthly fees?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse20" class="collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Yes, contact our sales team for details.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse21" aria-bs-expanded="false"
                                        aria-bs-controls="collapse21">
                                        How do I view my transaction history and fees?
                                    </button>
                                </h2>
                            </div>
                            <div id="collapse21" class="collapse" aria-labelledby="headingThree"
                                data-bs-parent="#accordionExample">
                                <div class="card-body">
                                Login to your Khushipay merchant dashboard.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
