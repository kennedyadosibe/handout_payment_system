<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    redirect('/Handout%20Payment%20System/payment-result.php?order=' . urlencode(trim($_POST['order_reference'] ?? '')));
}

page_header('Receipt Lookup', 'receipt');
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <form method="post" class="bg-white border rounded-2 p-4">
                <h1 class="h3">Find receipt</h1>
                <p class="text-muted">Enter your order reference to check payment and collection status.</p>
                <label class="form-label" for="order_reference">Order reference</label>
                <input class="form-control form-control-lg" id="order_reference" name="order_reference" placeholder="ORD-..." required>
                <button class="btn btn-primary btn-lg w-100 mt-3" type="submit">Look up order</button>
            </form>
        </div>
    </div>
</main>
<?php page_footer(); ?>
