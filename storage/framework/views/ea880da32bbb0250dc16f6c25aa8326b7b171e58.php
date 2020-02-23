<?php $__env->startSection('content'); ?>
<div class="container-fluid no-pd">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="row">

                <div class="agr_fld1 col-md-12">
                    <form action="/shop" class="container">
                        <div class="form-row agr-fade-it">
                            <div class="col-md-12 mb-2 mb-md-0">
                            <label for="search bar" class="agr-lbl">Partnering with Communities, Supporting Local Farmers</label>
                            <div class="agr-srch-br agr-fade-it">
                                <input type="text" class="form-control form-control-lg" id="search" placeholder="Search your product ...">
                                <button type="submit" class="btn btn-block btn-lg btn-success" id="find"><i class="fas fa-search"></i></button>
                            </div>
                                
                        </div>
                           
                           
                        </div>
                    </form>
                    <div class="container content mb-5 rounded agr-cstm-lnks agr-fade-it">

                        <?php $__currentLoopData = $categories->chunk(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="links">
                                <?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="/shop" class="findCategory  mt-2 ml-2" data-id="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            
                    </div>

                </div>
                <div class="agr-ads-fld agr-fade-it text-center">
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Display1-fixed728x90 -->
                    <ins class="adsbygoogle"
                        style="display:inline-block;width:728px;height:90px"
                        data-ad-client="ca-pub-2684224008431001"
                        data-ad-slot="7305056051"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
                <div class="col-md-12 col-lg-12 col-xl-12 mx-auto mb-5" style="padding:5px;">
                    <section class="content-header">
                        <h4 class="text-center agr-fld-ttle">Featured Products</h4>
                    </section>
  
                    <div class="agr-fld-fp">
                        <?php $__currentLoopData = $featprod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fprod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="agr-fp-parnt">
                            <div class="agr-fp-prdcts shadow-lg">
                                <img class="group list-group-image img-fluid" src="/img/product/<?php echo e($fprod->photo); ?>">
                                <a href="product/<?php echo e($fprod->slug); ?>&=<?php echo e($fprod->id); ?>&=<?php echo e($fprod->sku); ?>" class="agr-fp-label"><?php echo e($fprod->name); ?> <br><small><?php echo e($fprod->variety); ?></small></a>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        <div class="text-center"><a href="/shop" class="arg-vap-btn">View All Products</a></div> 
                    </div>   

                </div>

                <div class="col-md-12  col-xl-12 mx-auto mb-5 agr-fld-op" style="padding:5px;">
                    <section class="content-header">
                        <h4 class="text-center agr-fld-ttle">Our Partners</h4>
                    </section>
                    <div class="container">
                    <div class="glider text-center p-3  mb-2 rounded"  id="glider-partners">
                        <div><a href="/store/batangas-organic-and-natural-farming-agriculture-cooperative"><img class="group list-group-image img-fluid" width="150px" src="/img/home/agro-img_partners_09.png"></a></div>
                        <div><a href="/store/alfonso-cavite-organic-farmers-agriculture-cooperative"><img class="group list-group-image img-fluid" width="150px" src="/img/home/agro-img_partners_03.png"></a></div>
                        <div><a href="/store/cavite-responsible-organic-producers"><img class="group list-group-image img-fluid" width="150px" src="/img/home/agro-img_partners_11.png"></a></div>
                        <div><a href="/store/all-seasons-nature-farms"><img class="group list-group-image img-fluid" width="150px" src="/img/home/agro-img_partners_05.png"></a></div>
                        <div><a href="/store/luntiang-republika-ecofarms-corporation"><img class="group list-group-image img-fluid" width="150px" src="/img/home/agro-img_partners_10.png"></a></div>
                    </div>
                    </div>
                    <button role="button" aria-label="Previous" class="glider-prev" id="glider-prev"><i class="fa fa-chevron-left"></i></i></button>
                    <button role="button" aria-label="Next" class="glider-next" id="glider-next"><i class="fa fa-chevron-right"></i></i></button>
                    <div class="text-center mrgn-bot"><a href="/seller" class="arg-vp-btn">View All Partners</a></div>
                </div>

                <div class="col-md-12 col-lg-12 col-xl-12 mx-auto agr-fld-oc" style="padding:5px;">
                    <section class="content-header">
                        <h4 class="text-center agr-fld-ttle">Our Communities</h4>
                    </section>
                    <!--div class="container">
                    <div class="glider text-center shadow-sm p-3  mb-2 bg-white rounded" id="glider-communities">
                        <div><a href="/espclientele/aav"><img class="group list-group-image img-fluid" width="150px" src="/img/clientele/aav.png"></a></div>
                        <div><a href="/espclientele/brookside"><img class="group list-group-image img-fluid" width="150px" src="/img/clientele/brookside.jpg"></a></div>
                        <div><a href="/espclientele/theglens"><img class="group list-group-image img-fluid" width="150px" src="/img/clientele/theglens.jpg"></a></div>
                        <div><a href="/espclientele/sacredheart"><img class="group list-group-image img-fluid" width="150px" src="/img/clientele/sacredheart.jpeg"></a></div>
                    </div>
                    </div>
                    <button role="button" aria-label="Previous" class="glider-prev" id="glider-prev"><i class="fa fa-chevron-left"></i></i></button>
                    <button role="button" aria-label="Next" class="glider-next" id="glider-next"><i class="fa fa-chevron-right"></i></i></button>
                    <div class="text-right"><a href="/consortium" style="color:#2BA745;font-size:12px;">View All Communities</a></div>
                </div -->
                <div class="agr-fld-oc-lst container">
                    <ul class="agr-fld-oc-lst-cntnr">
                        <li class="agr-flex agr-tright">
                            <a href="/espclientele/aav"><img class="agr-fld-oc-l" src="/img/home/agro-oc-img_09.png" alt="aav is home"></a>
                            <a href="/espclientele/brookside"><img class="agr-fld-oc-l" src="/img/home/agro-oc-img_15.png" alt="brookside"></a>
                        </li>
                        <li class="agr-flex2 agr-center">
                            <a href="espclientele/ricepinas"><img class="agr-fld-oc-l agr-cstm-img" src="/img/home/agro-oc-img_03_ricepinas.png" alt="ricepinas"></a>           
                        </li>
                        <li class="agr-flex agr-tleft">
                            <a href="/espclientele/theglens"><img class="agr-fld-oc-l" src="/img/home/agro-oc-img_06.png" alt="parkspring"></a>
                            <a href="/espclientele/sacredheart"><img class="agr-fld-oc-l" src="/img/home/agro-oc-img_13.png" alt="sacred heart"></a>
                        </li>
                    </ul>
                    <div class="text-center"><a href="/consortium" class="arg-vp-btn">View All Communities</a></div><br><br>
              
                </div>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(asset('js/glider.min.js')); ?>"></script>
<script>
      
$(document).ready(function () {
    /** Carousel */
  /*  new Glider(document.getElementById('glider-featprod'), {
        slidesToShow: 4,
        dots: '#dots',
        draggable: false,
        arrows: {
            prev: '.glider-prev',
            next: '.glider-next'
        }
    });
*/
    new Glider(document.getElementById('glider-partners'), {
        slidesToShow: 5,
        dots: '#dots',
        draggable: false,
        gap: 30,
        arrows: {
            prev: '.glider-prev',
            next: '.glider-next'
        }
    });

    // new Glider(document.getElementById('glider-communities'), {
    //     slidesToShow: 4,
    //     dots: '#dots',
    //     draggable: false,
    //     arrows: {
    //         prev: '.glider-prev',
    //         next: '.glider-next'
    //     }
    // });

    function test() {
        console.log('asda');
    }
    
    
    /** Localstorage */
    const SEARCH_KEY = 'search-storage';
  
    $("#find").click(function() {
        let search = $('#search').val();
        if (search) {
            let q = [{'q': search, 'category': null}];
            localStorage.setItem(SEARCH_KEY, JSON.stringify(q));
        } else {
            localStorage.removeItem(SEARCH_KEY);
        }
    });

    $(".findCategory").click(function() {
        let category = $(this).data('id');
        let search = $('#search').val();
        let q = [{'q': search, 'category': category}];
        localStorage.setItem(SEARCH_KEY, JSON.stringify(q));
    });

    let storageSearch = localStorage.getItem(SEARCH_KEY) ? JSON.parse(localStorage.getItem(SEARCH_KEY)) : false;
    if (storageSearch) {
        document.getElementById("search").value = storageSearch[0].q;
        let search = $('#search').val();
        let q = [{'q': search, 'category': null}];
        localStorage.setItem(SEARCH_KEY, JSON.stringify(q));
    }

});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>