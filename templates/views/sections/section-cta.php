  <!-- Call To Action -->
      <section class="section bg-dark ">
        <div class="container pt-5">
          <div
            class="d-flex flex-lg-row flex-column-reverse  g-0 pt-5 mt-lg-5 mt-0 bg-primary bg-with-cover"
            style="
              background-color: transparent;
              background-image: linear-gradient(
                240deg,
                var(--primary-color) 77%,
                #353739 0%
              );
            "
          >
            <div class="col col-lg-4">
              <img
                src="<?=$urls->get('images'); ?>cotizar-stand.jpg"
                alt="Stand para exposición"
                class="img-fluid"
                style="margin-top: -35%"
              />
            </div>
            <div class="col col-lg-8 mb-lg-0 mb-5">
              <div class="d-flex align-items-start flex-column gap-3 h-100 p-4 mb-lg-0 mb-5">
                <h2 class="m-0 text-white">¿PLANIFICAS PARTICIPAR EN UNA EXPOSICIÓN?</h2>
                <span
                  >Contáctanos para recibir una cotización detallada y asesoría personalizada de nuestros especialistas en stands.</span
                >
                <div>
                  <a href="<?= $pages->get(1123)->url ?>" class="btn btn-dark gap-3 text-nowrap"
                    >COTIZAR MI STAND
                    <i class="rtmicon rtmicon-arrow-up-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
