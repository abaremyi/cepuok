
    <!--Page Loader-->
    <div class="page-loader"></div>
    <!--Zmm Wrapper-->
    <div class="zmm-wrapper">
        <a href="#" class="zmm-close close"></a>
        <div class="zmm-inner bg-white typo-dark">
            <div class="text-center mobile-logo-part margin-bottom-30">
                <a href="<?= url() ?>" class="img-before"><img src="<?= img_url('logo-dark.png') ?>" class="img-fluid changeable-dark" width="170" height="51" alt="Logo"></a>
            </div>
            <div class="zmm-main-nav"> </div>
            <div class="search-form-wrapper margin-top-30">
                <form class="search-form" role="search">
                    <div class="input-group add-on">
                        <input class="form-control" placeholder="Search for.." name="srch-term" type="text">
                        <div class="input-group-btn">
                            <button class="btn btn-default search-btn" type="submit"><i class="ti-arrow-right"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Overlay Search -->
    <div class="overlay-search text-center hide">
        <a href="#" class="close close-light overlay-search-close"></a>
        <div class="search-form-wrapper">
            <form class="navbar-form search-form sliding-search-form" role="search">
                <div class="input-group add-on">
                    <input class="form-control" placeholder="Search for.." name="srch-term" type="text">
                    <div class="input-group-btn">
                        <button class="btn btn-default search-btn" type="submit"><i class="ti-arrow-right"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>