<?php namespace ProcessWire;
$heroImageBackground = getStaticImageUrl('hero-proyectos', ['fallback' => 'jpg']);
?>
<main>
        <section class="section position-relative">
        <div class="container">
            <div class="d-flex flex-column">
                <span class="fw-semibold">NUESTRO TRABAJO</span>
                <h1 class="heading-hero fw-bold">PROYECTOS REALIZADOS</h1>
                <div class="bg-dark p-3 border d-flex gap-2 w-fit-content">
                    <a href="<?= $pages->get(1)->url ?>">INICIO</a> -
                    <span>PROYECTOS</span>
                </div>
            </div>
        </div>

        <div class="container-fullwidth position-absolute top-0 start-0 end-0 bottom-0" style="z-index: -1">
            <div class="row h-100 row-cols-lg-2 row-cols-1 g-0">
                <div class="col">
                    <div class="d-flex justify-content-center h-100 bg-primary bg-with-cover p-lg-5 p-4"></div>
                </div>
                <div class="col">
                    <div class="overlay position-relative w-100 h-100" style="background-image: url(<?= $heroImageBackground ?>); background-size: cover; background-position: center;"></div>
                </div>
            </div>
        </div>
    </section>

<?php 

 
 
$allImages = $page->fld_images;
$perPage = 12;
$totalImages = $allImages->count();
$totalPages = max(1, (int) ceil($totalImages / $perPage));
$currentPage = max(1, (int) $input->get->int('pg'));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$start = ($currentPage - 1) * $perPage;
$images = $allImages->slice($start, $perPage);
 
?>


    <section class="section">
        <div class="container">
            <div class="d-flex flex-column gap-3 justify-content-center align-items-center text-center">
                <span class="subheading">NUESTROS PROYECTOS</span>
                <h1 class="heading">Mas de 100 proyectos completados exitosamente.</h1>
                <p>
                    En ASE Display hemos participado en cientos de eventos feriales y exposiciones en Mexico y Latinoamerica. Cada proyecto es un compromiso con la excelencia y la satisfaccion de nuestros clientes.
                </p>
                <div class="row row-cols-lg-3 row-cols-1 g-4">

                     <?php foreach($images as $image): ?>

            <?php
              $thumb = $image->size(600, 600);
              $large = $image->width(1920);
              $alt = $image->description ?: 'Stand para exposición';
            ?>
                    <div class="col">
                        <div class="image-gallery-wrapper">
                           
                                      <a
                    href="<?= $large->url ?>"
                    class="btn  h-100 w-100 object-fit-cover"
                    data-fslightbox="gallery-stands"
                    aria-label="Ver imagen grande"
                  >
                    
               
                                <?= renderStaticPicture($thumb, [
                                  'alt' => $sanitizer->entities($alt),
                                  'class' => 'img-fluid',
                                  'loading' => 'lazy',
                                  'decoding' => 'async',
                                ]) ?>
                                     </a>
                        </div>
                    </div>
               <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                  <nav class="mt-4" aria-label="Paginacion de proyectos">
                    <ul class="pagination justify-content-center mb-0">
                      <?php
                        $baseUrl = $page->url;
                        $prevUrl = $currentPage > 1 ? ($baseUrl . '?pg=' . ($currentPage - 1)) : '';
                        $nextUrl = $currentPage < $totalPages ? ($baseUrl . '?pg=' . ($currentPage + 1)) : '';
                      ?>

                      <li class="page-item<?= $prevUrl ? '' : ' disabled'; ?>">
                        <?php if ($prevUrl): ?>
                          <a class="page-link" href="<?= $prevUrl ?>" aria-label="Anterior">Anterior</a>
                        <?php else: ?>
                          <span class="page-link">Anterior</span>
                        <?php endif; ?>
                      </li>

                      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php $url = $baseUrl . '?pg=' . $i; ?>
                        <li class="page-item<?= $i === $currentPage ? ' active' : ''; ?>">
                          <?php if ($i === $currentPage): ?>
                            <span class="page-link"><?= $i ?></span>
                          <?php else: ?>
                            <a class="page-link" href="<?= $url ?>"><?= $i ?></a>
                          <?php endif; ?>
                        </li>
                      <?php endfor; ?>

                      <li class="page-item<?= $nextUrl ? '' : ' disabled'; ?>">
                        <?php if ($nextUrl): ?>
                          <a class="page-link" href="<?= $nextUrl ?>" aria-label="Siguiente">Siguiente</a>
                        <?php else: ?>
                          <span class="page-link">Siguiente</span>
                        <?php endif; ?>
                      </li>
                    </ul>
                  </nav>
                <?php endif; ?>
            </div>
        </div>
    </section>


<?php include(SECTIONS.'/section-clients.php'); ?>
<?php include(SECTIONS.'/section-cta.php'); ?>
</main>
