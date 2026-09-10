        </main>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/app.js"></script>
<?php if (isset($extra_js)): ?>
    <?php foreach ((array) $extra_js as $js): ?>
        <script src="<?= BASE_URL . $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
