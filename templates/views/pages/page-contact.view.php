<?php namespace ProcessWire; ?>
 <main>
        <!-- Hero Section -->
        <section class="section position-relative">
            <div class="container">
                <div class="d-flex flex-column">
                    <span class="fw-semibold">CONTÁCTANOS</span>
                    <h1 class="heading-hero fw-bold">CONTACTO</h1>
                    <div class="bg-dark p-3 border d-flex gap-2 w-fit-content">
                        <a href="<?= $pages->get(1)->url ?>">INICIO</a> -
                        <span>CONTACTO</span>
                    </div>
                </div>
            </div>

            <div class="container-full-width position-absolute top-0 start-0 end-0 bottom-0" style="z-index: -1">
                <div class="row h-100 row-cols-lg-2 row-cols-1 g-0">
                    <div class="col">
                        <div class="d-flex justify-content-center h-100 bg-primary bg-with-cover p-lg-5 p-4"></div>
                    </div>
                    <div class="col">
                        <div class="overlay position-relative w-100 h-100" style="background-image: url(<?=$urls->get('images'); ?>hero-contacto.jpg); background-size: cover; background-position: center;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form and Info Section -->
        <section class="section bg-dark light-scheme-section">
            <div class="container">
                <div class="row row-cols-lg-2 row-cols-1 g-5">
                    <!-- Contact Form -->
                    <div class="col">
                        <div class="d-flex flex-column gap-4 h-100 justify-content-center">
                            <div class="text-center text-lg-start">
                                <span class="subheading">CONTÁCTANOS</span>
                                <h1 class="heading">Entre en contacto con nosotros</h1>
                                <p class="">Deje su mensaje y uno de nuestros representantes lo contactará a la brevedad.</p>
                            </div>
                            
                           <?php 
                             $builder = wire('modules')->get('WebformBuilder');
 
if($builder) print $builder->renderWebformById(1133);
                             ?>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="col">
                        <div class="d-flex flex-column gap-4">
                            <div class="card bg-primary p-4">
                                <h3 class=" mb-4">Estamos ubicados en:</h3>
                                <div class="d-flex gap-4 align-items-start">
                                    <?= renderStaticPicture('logo-ase', ['fallback' => 'png', 'alt' => 'Ubicación ASE Display', 'class' => 'img-fluid', 'style' => 'max-width: 150px;', 'loading' => 'lazy', 'decoding' => 'async']) ?>
                                    <div class="">
                                        <p class="m-0">Jenufa MZ 102 LT 21</p>
                                        <p class="m-0">Miguel Hidalgo, Tláhuac</p>
                                        <p class="m-0">13200 Ciudad de México, CDMX</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row row-cols-1 g-4">
                                <div class="col">
                                    <div class="card bg-secondary-dark p-4 h-100">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.8rem;">
                                                <i class="fa-solid fa-phone"></i>
                                            </div>
                                            <div>
                                                <span class=" small">VENTAS</span>
                                                <h5 class="m-0">55 9133-9200</h5>
                                                 <h5 class="m-0">55 6111-2310</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card bg-secondary-dark p-4 h-100">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.8rem;">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </div>
                                            <div>
                                                <span class=" small">ENVÍANOS UN MENSAJE POR WHATSAPP</span>
                                                <div class="d-flex flex-column">
                                                                       <?php 
                                                $wa = wire('modules')->get('WhatsappButton');
                                                if($wa) echo $wa->renderButton();
                                                ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card bg-secondary-dark p-4 h-100">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.8rem;">
                                                <i class="fa-solid fa-envelope"></i>
                                            </div>
                                            <div>
                                                <span class=" small">EMAIL</span>
                                                <h5 class="m-0">info@asedisplay.com</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="section p-0">
            <div class="container-full-width">
                <div class="mb-3">
                    <iframe 
                        loading="lazy" 
                        class="maps overflow-hidden w-100" 
                        src="https://maps.google.com/maps?q=Jenufa+Mz+102+LT+21+Miguel+Hidalgo+Tláhuac+13200+Ciudad+de+México+CDMX&amp;t=m&amp;z=14&amp;output=embed&amp;iwloc=near" 
                        title="Ubicación ASE Display" 
                        aria-label="Ubicación ASE Display en Ciudad de México" 
                        height="400">
                    </iframe>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-primary py-5">
            <div class="container">
                <div class="row row-cols-lg-2 row-cols-1 align-items-center g-4">
                    <div class="col">
                        <div class="d-flex flex-column gap-3">
                            <h2 class="m-0 ">¿Necesitas ayuda?</h2>
                            <p class="m-0 ">Habla con una persona de ASEDisplay.com, podemos ayudarte.</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex flex-lg-row flex-column gap-3 justify-content-lg-end">
                            <a href="tel:+525591339200" class="btn btn-dark gap-2">
                                <i class="fa-solid fa-phone"></i>
                                55 9133-9200
                            </a>
                            <a href="https://wa.me/525592183323" class="btn btn-dark gap-2">
                                <i class="fa-brands fa-whatsapp"></i>
                                WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
