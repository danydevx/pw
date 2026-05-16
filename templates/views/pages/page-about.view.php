<?php namespace ProcessWire; ?>
 <main>
   <section class="section position-relative">
        <div class="container">
          <div class="d-flex flex-column">
            <span class="fw-semibold">JUNTOS CREAMOS STANDS QUE IMPACTAN</span>
            <h1 class="heading-hero fw-bold">SOBRE NOSOTROS</h1>
            <div class="bg-dark p-3 border d-flex gap-2 w-fit-content">
              <a href="<?= $pages->get(1)->url ?>">INICIO</a> -
              <span>SOBRE NOSOTROS</span>
            </div>
          </div>
        </div>

        <div
          class="container-full-width position-absolute top-0 start-0 end-0 bottom-0"
          style="z-index: -1"
        >
          <div class="row h-100 row-cols-lg-2 row-cols-1 g-0">
            <div class="col">
              <div
                class="d-flex justify-content-center h-100 bg-primary bg-with-cover p-lg-5 p-4"
              ></div>
            </div>
            <div class="col">
              <div
                class="overlay position-relative w-100 h-100"
                style="
                  background-image: url(<?=$urls->get('images'); ?>acerca-hero.jpg);
                  background-size: cover;
                  background-position: center;
                "
              ></div>
            </div>
          </div>
        </div>
      </section>

        <!-- Our Company -->
      <section class="bg-dark section pb-0 light-scheme-section">
        <div class="container">
          <div class="d-flex flex-lg-row flex-column gap-lg-0 gap-3">
            <div class="col">
              <div class="h-100 position-relative pe-lg-5 pe-0">
                <div
                  class="border border-primary border-dotted border-2 p-4 w-75"
                >

                <?= renderStaticPicture('acerca-ase-display', [
                  'fallback' => 'jpg',
                  'alt' => 'Stand custom para exposicion',
                  'class' => 'img-fluid w-100',
                ]) ?>
                  
                </div>
                <div class="position-absolute end-0 bottom-0 w-50">
                  <?= renderStaticPicture('acerca-ase-display-2', [
                    'fallback' => 'jpg',
                    'alt' => 'Montaje de stand en feria',
                    'class' => 'img-fluid image-mask-1',
                    'style' => 'border: solid 1.5rem var(--dark-color); -webkit-mask-size: 200%;',
                  ]) ?>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex px-lg-5 px-0 flex-column gap-3">
                <span class="subheading">NUESTRA EMPRESA</span>
                <h1>Tenemos la visión de crear stands que generan resultados.</h1>
                <p>
                  En ASE Display, cada stand que fabricamos refleja nuestro compromiso con la excelencia. Combinamos creatividad, materiales de calidad y técnicas innovadoras para crear espacios que ayudan a tu marca a destacar en ferias, exposiciones y eventos corporativos.
                </p>
                <div class="d-flex flex-lg-row flex-column">
                  <div class="col">
                    <div class="circle-wrapper">
                      <div class="circle-text" id="circleText">
                        --- MEJORES SERVICIOS Y CALIDAD --- MEJORES SERVICIOS Y CALIDAD ---
                      </div>
                      <a
                        href="#"
                        class="circle-button bg-primary  rounded-circle p-4"
                      >
                        <i
                          aria-hidden="true"
                          class="rtmicon-thin rtmicon-gear-house"
                          style="font-size: 3.75rem"
                        ></i>
                      </a>
                    </div>
                  </div>
                  <div class="col">
                    <div class="position-relative">
                      <?= renderStaticPicture($urls->get('tplAssets') . 'videos/videosPortada.png', [
                        'alt' => 'Stand para exposicion',
                        'class' => 'img-fluid',
                        'loading' => 'lazy',
                        'decoding' => 'async',
                      ]) ?>
                      <div class="position-absolute start-0 top-0 h-100 w-100">
                        <div
                          class="d-flex h-100 align-items-center justify-content-center"
                        >
                          <button
                            class="btn btn-video"
                            data-bs-toggle="modal"
                            data-bs-target="#videoModal"
                          >
                            <i class="fa-solid fa-play"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <ul class="d-flex flex-column gap-2 py-3">
                      <li>
                        <div class="d-flex flex-row align-items-center gap-2">
                          <i
                            class="primary-color rtmicon rtmicon-circle-check"
                          ></i>
                          <span>Diseñadores Especializados</span>
                        </div>
                      </li>
                      <li>
                        <div class="d-flex flex-row align-items-center gap-2">
                          <i
                            class="primary-color rtmicon rtmicon-circle-check"
                          ></i>
                          <span>Técnicos Certificados</span>
                        </div>
                      </li>
                      <li>
                        <div class="d-flex flex-row align-items-center gap-2">
                          <i
                            class="primary-color rtmicon rtmicon-circle-check"
                          ></i>
                          <span>Resultados que Superan Expectativas</span>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
                <hr class="border" />
                <div
                  class="d-flex flex-lg-row gap-5 flex-column align-items-center"
                >
                  <div class="d-flex gap-3 align-items-center">
                     
                    <div class="d-flex flex-column">
                      <h6 class="m-0">EQUIPO ASE DISPLAY</h6>
                      <p class="text-uppercase m-0">LÍDERES EN STANDS</p>
                    </div>
                  </div>
                  <div>
                    <a
                      href="<?= $pages->get(1129)->url ?>"
                      class="btn btn-primary gap-3 text-nowrap"
                      >COTIZA AHORA
                      <i class="rtmicon rtmicon-arrow-up-right"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- Modal -->
      <div
        class="modal fade"
        id="videoModal"
        tabindex="-1"
        aria-labelledby="videoModalLabel"
        aria-hidden="true"
      >
        <div class="modal-dialog modal-dialog-centered bg-dark" style="max-width: min(1000px, 95vw);">
          <div class="modal-content bg-dark">
              <div class="modal-header">
        <h5 class="modal-title">Xcaret</h5>
        <button type="button" class="btn-close text-white bg-white " data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
           <video controls style="display: block; width: 100%; height:auto; max-height: 60vh; ">
             <source src="<?=$urls->get('tplAssets'); ?>videos/videoAse.mp4" type="video/mp4">
             <source src="alternate-example.webm" type="video/webm">
           Tu navegador no es compatible con el vídeo de HTML5.
           </video>
          </div>
        </div>
      </div>
            <section class="bg-dark section light-scheme-section">
        <div class="container">
          <div class="row row-cols-lg-4 row-cols-sm-2 row-cols-1">
            <div class="col">
              <div class="d-flex flex-column align-items-center">
                <svg
                  class="svg-stroke fun-facts-icon svg-stroke-animation"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 640 512"
                >
                  <path
                    d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm448 0c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm32 32h-64c-17.6 0-33.5 7.1-45.1 18.6 40.3 22.1 68.9 62 75.1 109.4h66c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64zm-256 0c61.9 0 112-50.1 112-112S381.9 32 320 32 208 82.1 208 144s50.1 112 112 112zm76.8 32h-8.3c-20.8 10-43.9 16-68.5 16s-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48v-28.8c0-63.6-51.6-115.2-115.2-115.2zm-223.7-13.4C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"
                     fill="transparent"
                   ></path>
                </svg>
                <div class="d-flex align-items-center fun-facts gap-3">
                  <h1 class="fun-facts-number">
                    <span class="fun-facts-value">100</span>
                    <span class="fun-facts-suffix">+</span>
                  </h1>
                  <div class="fun-facts-text text-uppercase bg-primary p-2">
                    <span>Stands Fabricados</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex flex-column align-items-center">
                <svg
                  class="svg-stroke fun-facts-icon svg-stroke-animation"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 640 512"
                >
                  <path
                    d="M488 192H336v56c0 39.7-32.3 72-72 72s-72-32.3-72-72V126.4l-64.9 39C107.8 176.9 96 197.8 96 220.2v47.3l-80 46.2C.7 322.5-4.6 342.1 4.3 357.4l80 138.6c8.8 15.3 28.4 20.5 43.7 11.7L231.4 448H368c35.3 0 64-28.7 64-64h16c17.7 0 32-14.3 32-32v-64h8c13.3 0 24-10.7 24-24v-48c0-13.3-10.7-24-24-24zm147.7-37.4L555.7 16C546.9.7 527.3-4.5 512 4.3L408.6 64H306.4c-12 0-23.7 3.4-33.9 9.7L239 94.6c-9.4 5.8-15 16.1-15 27.1V248c0 22.1 17.9 40 40 40s40-17.9 40-40v-88h184c30.9 0 56 25.1 56 56v28.5l80-46.2c15.3-8.9 20.5-28.4 11.7-43.7z"
                     fill="transparent"
                   ></path>
                </svg>
                <div class="d-flex align-items-center fun-facts gap-3">
                  <h1 class="fun-facts-number">
                    <span class="fun-facts-value">90</span>
                    <span class="fun-facts-suffix">+</span>
                  </h1>
                  <div class="fun-facts-text text-uppercase p-2">
                    <span>Proyectos Exitosos</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex flex-column align-items-center">
                <svg
                  class="svg-stroke fun-facts-icon svg-stroke-animation"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 640 512"
                >
                  <path
                    d="M528 448H112c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm64-320c-26.5 0-48 21.5-48 48 0 7.1 1.6 13.7 4.4 19.8L476 239.2c-15.4 9.2-35.3 4-44.2-11.6L350.3 85C361 76.2 368 63 368 48c0-26.5-21.5-48-48-48s-48 21.5-48 48c0 15 7 28.2 17.7 37l-81.5 142.6c-8.9 15.6-28.9 20.8-44.2 11.6l-72.3-43.4c2.7-6 4.4-12.7 4.4-19.8 0-26.5-21.5-48-48-48S0 149.5 0 176s21.5 48 48 48c2.6 0 5.2-.4 7.7-.8L128 416h384l72.3-192.8c2.5.4 5.1.8 7.7.8 26.5 0 48-21.5 48-48s-21.5-48-48-48z"
                     fill="transparent"
                   ></path>
                </svg>
                <div class="d-flex align-items-center fun-facts gap-3">
                  <h1 class="fun-facts-number">
                    <span class="fun-facts-value">10</span>
                    <span class="fun-facts-suffix">+</span>
                  </h1>
                  <div class="fun-facts-text text-uppercase p-2">
                    <span>Años de Experiencia</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex flex-column align-items-center">
                <svg
                  class="svg-stroke fun-facts-icon svg-stroke-animation"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 512 512"
                  height="80"
                >
                  <path
                    d="M502.63 214.63l-45.25-45.25c-6-6-14.14-9.37-22.63-9.37H384V80c0-26.51-21.49-48-48-48H176c-26.51 0-48 21.49-48 48v80H77.25c-8.49 0-16.62 3.37-22.63 9.37L9.37 214.63c-6 6-9.37 14.14-9.37 22.63V320h128v-16c0-8.84 7.16-16 16-16h32c8.84 0 16 7.16 16 16v16h128v-16c0-8.84 7.16-16 16-16h32c8.84 0 16 7.16 16 16v16h128v-82.75c0-8.48-3.37-16.62-9.37-22.62zM320 160H192V96h128v64zm64 208c0 8.84-7.16 16-16 16h-32c-8.84 0-16-7.16-16-16v-16H192v16c0 8.84-7.16 16-16 16h-32c-8.84 0-16-7.16-16-16v-16H0v96c0 17.67 14.33 32 32 32h448c17.67 0 32-14.33 32-32v-96H384v16z"
                     fill="transparent"
                   ></path>
                </svg>
                <div class="d-flex align-items-center fun-facts gap-3">
                  <h1 class="fun-facts-number">
                    <span class="fun-facts-value">70</span>
                    <span class="fun-facts-suffix">+</span>
                  </h1>
                  <div class="fun-facts-text text-uppercase p-2">
                    <span>Marcas Atendidas</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

            <!-- why choose Us -->
      <section class="section bg-dark light-scheme-section">
        <div class="container">
          <div class="row row-cols-lg-2 row-cols-1 border g-0 mb-3">
            <div class="col">
              <div class="d-flex flex-column gap-3 p-4">
                <span class="subheading">¿POR QUÉ ELEGIRNOS?</span>
                <h1 class="heading">
                  Somos la mejor opción en diseño y fabricación de stands.
                </h1>
                <div>
                  <p>
                    Con nuestra experiencia especializada y soluciones personalizadas, entregamos resultados que superan expectativas. Cada stand que fabricamos ayuda a tu marca a destacar en ferias y exposiciones.
                  </p>
                </div>
                <div class="d-flex flex-lg-row flex-column gap-5">
                  <div>
                   <a
                      href="<?= $pages->get(1129)->url ?>"
                      class="btn btn-primary gap-3 text-nowrap"
                      >COTIZA AHORA
                      <i class="rtmicon rtmicon-arrow-up-right"></i>
                    </a>
                  </div>
                  <div class="d-flex gap-3">
                    <i
                      aria-hidden="true"
                      class="primary-color rtmicon-thin rtmicon-comments-question"
                      style="font-size: 3.2rem"
                    ></i>
                    <div class="d-flex flex-column">
                      <span class="primary-color">¿Tienes alguna duda?</span>
                      <h5>55 9133-9200</h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex flex-column gap-3 p-5">
                <h5>NUESTRAS HABILIDADES</h5>
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex justify-content-between">
                    <h6>DISEÑO DE STANDS</h6>
                    <span>100%</span>
                  </div>
                  <div class="progress-bar" style="--progress: 100"></div>
                </div>
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex justify-content-between">
                    <h6>FABRICACIÓN</h6>
                    <span>100%</span>
                  </div>
                  <div class="progress-bar" style="--progress: 100"></div>
                </div>
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex justify-content-between">
                    <h6>MONTAJE PROFESIONAL</h6>
                    <span>100%</span>
                  </div>
                  <div class="progress-bar" style="--progress: 100"></div>
                </div>
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex justify-content-between">
                    <h6>ATENCIÓN AL CLIENTE</h6>
                    <span>100%</span>
                  </div>
                  <div class="progress-bar" style="--progress: 100"></div>
                </div>
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex justify-content-between">
                    <h6>GESTIÓN DE PROYECTOS</h6>
                    <span>100%</span>
                  </div>
                  <div class="progress-bar" style="--progress: 100"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="row row-cols-lg-3 row-cols-1 g-0 p-0">
            <div class="col">
              <div class="card card-hover border border-dark p-4">
                <div class="d-flex flex-column p-2 gap-4">
                  <div class="icon-box">
                    <i
                      aria-hidden="true"
                      class="rtmicon-thin rtmicon-home-renovation"
                    ></i>
                  </div>
                  <span class="subheading ms-4">SOMOS ESPECIALISTAS</span>
                  <p>
                    Más de 15 años de experiencia en el diseño, fabricación y montaje de stands para exposiciones y ferias.
                  </p>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card card-hover border border-dark p-4">
                <div class="d-flex flex-column p-2 gap-4">
                  <div class="icon-box">
                    <i
                      aria-hidden="true"
                      class="rtmicon-thin rtmicon-design-development"
                    ></i>
                  </div>
                  <span class="subheading ms-4">SOMOS ÍNTEGROS</span>
                  <p>
                    Cumplimos con los plazos acordados y entregamos trabajos de alta calidad sin comprometer ningún detalle.
                  </p>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card card-hover border border-dark p-4">
                <div class="d-flex flex-column p-2 gap-4">
                  <div class="icon-box">
                    <i
                      aria-hidden="true"
                      class="rtmicon-thin rtmicon-involved"
                    ></i>
                  </div>
                  <span class="subheading ms-4">SOMOS EXPERIMENTADOS</span>
                  <p>
                    Hemos fabricado y montado stands para cientos de marcas en las principales exposiciones de México.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

          <?php include(SECTIONS.'/section-cta.php'); ?>
  </main>
