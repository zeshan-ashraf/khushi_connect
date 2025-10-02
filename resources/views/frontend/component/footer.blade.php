<!-- footer -->
<footer class="footer-bg footer-p" style="background-color: #303539; background-image: url(img/bg/footer-bg.png);">
    <div class="footer-top-heiding">
       <div class="container">
           <div class="row align-items-center">
               <div class="col-lg-4 col-md-6">
                   <div class="f-contact">
                       <div class="icon">
                            <img src="{{asset('assets/img/icon/ft-icon01.png')}}" alt="img">
                       </div>
                       <div class="text">
                           <span> Shop#5 Central Plaza Barkat Market, Garden Town, Lahore Pakistan</span>
                           <h3>Our Location:</h3>
                       </div>

                   </div>
               </div>
                <div class="col-lg-4 col-md-6">
                    <div class="f-contact">
                       <div class="icon">
                            <img src="{{asset('assets/img/icon/ft-icon02.png')}}" alt="img">
                       </div>
                       <div class="text">
                           <span>info@khushipay.com</span>
                           <h3> Our Email:</h3>
                       </div>

                   </div>
               </div>
                <div class="col-lg-4 col-md-6">
                    <div class="f-contact">
                       <div class="icon">
                            <img src="{{asset('assets/img/icon/ft-icon03.png')}}" alt="img">
                       </div>
                       <div class="text">
                            <span>+92 327 4920565</span>
                           <h3>Call Now:</h3>

                       </div>

                   </div>
               </div>

           </div>
       </div>

   </div>

   <div class="footer-top pb-70">
       <div class="container">
           <div class="row justify-content-between">

               <div class="col-xl-6 col-lg-6 col-sm-6">
                   <div class="footer-widget mb-30">
                       <div class="f-widget-title">
                           <h2><img src="{{asset('assets/img/logo/footer.png')}}" alt="img"></h2>
                       </div>
                       <div class="f-contact">
                            <p>It’s time to go cashless. We offer a comprehensive payment solution that lets your business manage cash flows, present digital invoices and receive digital payments through a fast, easy and reliable one-window interface. Your clients can opt payment methods of their choice which include Digital Wallets (Jazzcash, Easypaisa), digital bank transfers, debit/credit cards etc. Get Registered for Free</p>

                           </div>

                   </div>
               </div>
               <div class="col-xl-3 col-lg-3 col-sm-6">
                   <div class="footer-widget mb-30">
                       <div class="f-widget-title">
                           <h2>Our Links</h2>
                       </div>
                       <div class="footer-link">
                           <ul>
                               <li><a href="{{route('home')}}">Home</a></li>
                               <li><a href="{{route('about')}}">About Us</a></li>
                               <li><a href="{{route('contact')}}">Contact Us</a></li>
                               <li><a href="{{route('services')}}">Services </a></li>
                               <li><a href="{{route('pricing')}}">Pricing</a></li>
                           </ul>
                       </div>
                   </div>
               </div>
               <div class="col-xl-3 col-lg-3 col-sm-6">
                   <div class="footer-widget mb-30">
                       <div class="f-widget-title">
                         <h2>Subscribe Now !</h2>
                       </div>
                       <div class="footer-link">
                        <div class="subricbe p-relative" data-animation="fadeInDown" data-delay=".4s" >
                                   <form action="news-mail.php" method="post" class="contact-form ">
                                    <input type="text" id="email2" name="email2"  class="header-input" placeholder="Your Email..." required>
                                   <button class="btn header-btn"><i class="fas fa-location-arrow"></i></button>
                                   </form>
                               </div>
                       </div>

                   </div>
                   <div class="footer-social mt-10">
                           <a href="#"><i class="fab fa-facebook-f"></i></a>
                           <a href="#"><i class="fab fa-instagram"></i></a>
                           <a href="#"><i class="fab fa-twitter"></i></a>
                       </div>
               </div>

           </div>
       </div>
   </div>
   <div class="copyright-wrap">
       <div class="container">
           <div class="row align-items-center">
               <div class="col-lg-4">
                 <div class="copy-text">
                        Copyright &copy; Khushipay 2024 . All rights reserved.
                   </div>
               </div>
               <div class="col-lg-2 text-center">


               </div>
              <div class="col-lg-6 text-right text-xl-right">
                  <ul>
                      <li><a href="{{route('privacy-policy')}}">Privacy Policy</a></li>
                      <li><a href="{{route('terms-condition')}}">Term &amp; Conditions</a></li>
                      <li><a href="{{route('refund-policy')}}">Refund Policy</a></li>
                  </ul>
               </div>
           </div>
       </div>
   </div>
</footer>
<!-- footer-end -->
