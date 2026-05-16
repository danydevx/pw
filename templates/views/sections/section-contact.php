<?php namespace ProcessWire; ?>
<!-- Contact Us -->
      <section
        class="section section-overlay pb-0 light-scheme-section"
        style="background-image: url(<?=$urls->get('images'); ?>dummy-image-1920x1280.jpg)"
      >
        <div
          class="container border-dotted border-start-0 border-end-0 border-bottom-0 border-primary p-5 bg-dark"
        >
          <div class="row row-cols-lg-2 row-cols-1 g-5">
            <div class="col col-lg-5">
              <div class="d-flex flex-column gap-3">
                <h2>Contáctanos</h2>
                <p>
                  Si necesitas cotizar un stand o tienes dudas sobre nuestros servicios, comunícate con nuestros especialistas en diseño y fabricación de stands.
                </p>
                <div class="d-flex flex-column gap-3">
                  <div
                    class="d-flex align-items-center gap-4 bg-secondary-dark p-4"
                  >
                    <div
                      class="bg-primary d-flex align-items-center justify-content-center text-center"
                      style="width: 65px; height: 65px; font-size: 2.5rem"
                    >
                      <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <span>NUESTRA DIRECCIÓN</span>
                      <h5>Ciudad de México, México</h5>
                    </div>
                  </div>
                  <div
                    class="d-flex align-items-center gap-4 bg-secondary-dark p-4"
                  >
                    <div
                      class="bg-primary d-flex align-items-center justify-content-center text-center"
                      style="width: 65px; height: 65px; font-size: 2.5rem"
                    >
                      <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <span>ESCRÍBENOS</span>
                      <h5>info@asedisplay.com</h5>
                    </div>
                  </div>
                  <div
                    class="d-flex align-items-center gap-4 bg-secondary-dark p-4"
                  >
                    <div
                      class="bg-primary d-flex align-items-center justify-content-center text-center"
                      style="width: 65px; height: 65px; font-size: 2.5rem"
                    >
                      <i class="fa-solid fa-phone"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <span>LLÁMANOS</span>
                      <h5>55 9133-9200</h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col col-lg-7">
                             <?php 
                             $builder = wire('modules')->get('WebformBuilder');
 
if($builder) print $builder->renderWebformById(1133);
                             ?>
            </div>
          </div>
        </div>
        <div
          class="success_msg bg-dark glass-effect position-fixed bottom-0 end-0 toast align-items-center w-100 mb-3 rounded-0 my-4 w-fit-content"
          id="liveToast"
          role="alert"
          aria-live="assertive"
          aria-atomic="true"
        >
          <div class="d-flex p-2">
            <div
              class="toast-body f-18 d-flex flex-row gap-3 align-items-center text-success"
            >
              <i class="fa-solid fa-check f-36 text-success"></i>
              Tu mensaje ha sido enviado exitosamente.
            </div>
            <button
              type="button"
              class="me-2 m-auto bg-transparent border-0 ps-1 pe-0 text-success"
              data-bs-dismiss="toast"
              aria-label="Close"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>
      </section>
