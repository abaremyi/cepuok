<?php
/**
 * Admin Footer Layout
 * File: layouts/admin-footer.php
 */
?>
<div class="footer">
    <div class="row justify-content-between align-items-center">
        <div class="col">
            <p class="fs-6 mb-0">&copy; <?= date('Y') ?> CEP UoK. All rights reserved.</p>
        </div>
        <div class="col-auto">
            <div class="d-flex justify-content-end">
                <ul class="list-inline list-separator mb-0">
                    <li class="list-inline-item">
                        <a class="list-separator-link" href="<?= BASE_URL ?>/privacy">Privacy</a>
                    </li>
                    <li class="list-inline-item">
                        <a class="list-separator-link" href="<?= BASE_URL ?>/terms">Terms</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>