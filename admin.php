<?php
// --- الإعدادات الأساسية ---
$password = '123456'; // <--- غيّر كلمة المرور هذه!
$products_file = 'products.json';
$message = '';

// --- منطق حفظ البيانات ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password']) && $_POST['password'] === $password) {
        $products_data = [];
        if (isset($_POST['products'])) {
            foreach ($_POST['products'] as $p) {
                $product = [
                    'id' => $p['id'] ?: uniqid(),
                    'name' => $p['name'],
                    'category' => $p['category'],
                    'description' => $p['description'],
                    'basePrice' => (float)$p['basePrice'],
                    'image' => $p['image'],
                    'attributes' => isset($p['attributes']) ? array_values($p['attributes']) : [],
                    'customizations' => []
                ];

                if (isset($p['customizations'])) {
                    foreach ($p['customizations'] as $c) {
                        $customization = [
                            'groupName' => $c['groupName'],
                            'type' => $c['type'],
                            'name' => $c['name'] ?: uniqid('cust_'),
                            'required' => isset($c['required']),
                            'options' => []
                        ];
                        if (isset($c['options'])) {
                            foreach ($c['options'] as $o) {
                                if (!empty($o['text'])) {
                                    $customization['options'][] = [
                                        'text' => $o['text'],
                                        'value' => $o['value'] ?: str_replace(' ', '_', $o['text']),
                                        'price' => (float)$o['price']
                                    ];
                                }
                            }
                        }
                        $product['customizations'][] = $customization;
                    }
                }
                $products_data[] = $product;
            }
        }
        
        // كتابة البيانات الجديدة في ملف JSON
        $json_data = json_encode($products_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($products_file, $json_data)) {
            $message = '<div class="message success">تم حفظ التغييرات بنجاح!</div>';
        } else {
            $message = '<div class="message error">فشل في حفظ الملف. تأكد من صلاحيات الكتابة للملف.</div>';
        }
    } else {
        $message = '<div class="message error">كلمة المرور غير صحيحة.</div>';
    }
}

// --- قراءة البيانات الحالية لعرضها في النموذج ---
$products = [];
if (file_exists($products_file)) {
    $products = json_decode(file_get_contents($products_file), true);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم المنتجات</title>
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; direction: rtl; }
        .container { max-width: 900px; margin: 20px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #d87525; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        form input, form select, form textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .product-card { background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px; position: relative; }
        .product-card h3 { margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: 1 / -1; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1em; }
        .btn-save { background-color: #28a745; color: white; width: 100%; font-size: 1.2em; padding: 15px; }
        .btn-add { background-color: #d87525; color: white; margin-bottom: 20px; }
        .btn-remove { background-color: #e74c3c; color: white; padding: 5px 10px; font-size: 0.8em; position: absolute; top: 15px; left: 15px; }
        .customization-group { background: #fff; border: 1px dashed #ccc; padding: 15px; margin-top: 15px; border-radius: 5px; }
        .option-item { display: flex; gap: 10px; align-items: center; }
        .option-item input { margin-bottom: 0; }
        .password-section { text-align: center; margin-bottom: 20px; }
        .password-section input { width: 300px; display: inline-block; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <h1>لوحة تحكم المنتجات</h1>
    <?= $message ?>

    <form method="post" action="admin.php">
        <div id="products-container">
            <?php foreach ($products as $i => $product): ?>
            <div class="product-card">
                <button type="button" class="btn btn-remove" onclick="this.parentElement.remove()">إزالة المنتج</button>
                <h3>المنتج #<?= $i + 1 ?></h3>
                <input type="hidden" name="products[<?= $i ?>][id]" value="<?= htmlspecialchars($product['id']) ?>">
                <div class="form-grid">
                    <input type="text" name="products[<?= $i ?>][name]" placeholder="اسم المنتج" value="<?= htmlspecialchars($product['name']) ?>" required>
                    <input type="text" name="products[<?= $i ?>][category]" placeholder="الفئة" value="<?= htmlspecialchars($product['category']) ?>" required>
                    <input type="number" step="0.01" name="products[<?= $i ?>][basePrice]" placeholder="السعر الأساسي" value="<?= htmlspecialchars($product['basePrice']) ?>" required>
                    <div class="full-width"><input type="text" name="products[<?= $i ?>][image]" placeholder="رابط الصورة" value="<?= htmlspecialchars($product['image']) ?>" required></div>
                    <div class="full-width"><textarea name="products[<?= $i ?>][description]" placeholder="الوصف"><?= htmlspecialchars($product['description']) ?></textarea></div>
                    <!-- ... (Attributes and Customizations will be added here) ... -->
                </div>
                <div class="customizations-container">
                    <!-- Customizations will be rendered here -->
                </div>
                <button type="button" class="btn" onclick="addCustomizationGroup(this)">إضافة مجموعة تخصيص</button>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-add" id="add-product-btn">إضافة منتج جديد</button>
        
        <div class="password-section">
            <input type="password" name="password" placeholder="كلمة المرور" required>
        </div>
        
        <button type="submit" class="btn btn-save">حفظ كل التغييرات</button>
    </form>
</div>

<!-- Templates for JavaScript -->
<template id="product-template">
    <div class="product-card">
        <button type="button" class="btn btn-remove" onclick="this.parentElement.remove()">إزالة المنتج</button>
        <h3>منتج جديد</h3>
        <input type="hidden" name="products[][id]" value="">
        <div class="form-grid">
            <input type="text" name="products[][name]" placeholder="اسم المنتج" required>
            <input type="text" name="products[][category]" placeholder="الفئة" required>
            <input type="number" step="0.01" name="products[][basePrice]" placeholder="السعر الأساسي" required>
            <div class="full-width"><input type="text" name="products[][image]" placeholder="رابط الصورة" required></div>
            <div class="full-width"><textarea name="products[][description]" placeholder="الوصف"></textarea></div>
        </div>
        <div class="customizations-container"></div>
        <button type="button" class="btn" onclick="addCustomizationGroup(this)">إضافة مجموعة تخصيص</button>
    </div>
</template>

<template id="customization-template">
    <div class="customization-group">
        <button type="button" class="btn btn-remove" onclick="this.parentElement.remove()">إزالة المجموعة</button>
        <h4>مجموعة تخصيص</h4>
        <div class="form-grid">
            <input type="text" name="" placeholder="عنوان المجموعة (مثال: الحجم)" required>
            <input type="text" name="" placeholder="الاسم البرمجي (انجليزي بدون مسافات)" required>
            <select name="">
                <option value="radio">اختيار واحد (radio)</option>
                <option value="checkbox">عدة اختيارات (checkbox)</option>
            </select>
            <label><input type="checkbox" name=""> إجباري</label>
        </div>
        <div class="options-container"></div>
        <button type="button" class="btn" onclick="addOption(this)">إضافة خيار</button>
    </div>
</template>

<template id="option-template">
    <div class="option-item">
        <input type="text" name="" placeholder="اسم الخيار (مثال: كبير)" required>
        <input type="text" name="" placeholder="القيمة (انجليزي)">
        <input type="number" step="0.01" name="" placeholder="زيادة السعر (0 للمجاني)">
        <button type="button" class="btn btn-remove" onclick="this.parentElement.remove()">-</button>
    </div>
</template>


<script>
    let productIndex = <?= count($products) ?>;
    
    document.getElementById('add-product-btn').addEventListener('click', () => {
        const template = document.getElementById('product-template');
        const clone = template.content.cloneNode(true);
        const productCard = clone.querySelector('.product-card');
        
        // Update names for the new product
        productCard.querySelectorAll('[name^="products[]"]').forEach(el => {
            el.name = el.name.replace('[]', `[${productIndex}]`);
        });

        document.getElementById('products-container').appendChild(clone);
        productIndex++;
    });

    function addCustomizationGroup(button) {
        const productCard = button.closest('.product-card');
        const container = productCard.querySelector('.customizations-container');
        const productIdx = Array.from(document.querySelectorAll('.product-card')).indexOf(productCard);
        const custIndex = container.querySelectorAll('.customization-group').length;

        const template = document.getElementById('customization-template');
        const clone = template.content.cloneNode(true);

        clone.querySelector('[placeholder="عنوان المجموعة (مثال: الحجم)"]').name = `products[${productIdx}][customizations][${custIndex}][groupName]`;
        clone.querySelector('[placeholder="الاسم البرمجي (انجليزي بدون مسافات)"]').name = `products[${productIdx}][customizations][${custIndex}][name]`;
        clone.querySelector('select').name = `products[${productIdx}][customizations][${custIndex}][type]`;
        clone.querySelector('input[type="checkbox"]').name = `products[${productIdx}][customizations][${custIndex}][required]`;

        container.appendChild(clone);
    }

    function addOption(button) {
        const custGroup = button.closest('.customization-group');
        const container = custGroup.querySelector('.options-container');
        const productCard = custGroup.closest('.product-card');
        const productIdx = Array.from(document.querySelectorAll('.product-card')).indexOf(productCard);
        const custIndex = Array.from(productCard.querySelectorAll('.customization-group')).indexOf(custGroup);
        const optionIndex = container.querySelectorAll('.option-item').length;

        const template = document.getElementById('option-template');
        const clone = template.content.cloneNode(true);

        clone.querySelector('[placeholder="اسم الخيار (مثال: كبير)"]').name = `products[${productIdx}][customizations][${custIndex}][options][${optionIndex}][text]`;
        clone.querySelector('[placeholder="القيمة (انجليزي)"]').name = `products[${productIdx}][customizations][${custIndex}][options][${optionIndex}][value]`;
        clone.querySelector('[placeholder="زيادة السعر (0 للمجاني)"]').name = `products[${productIdx}][customizations][${custIndex}][options][${optionIndex}][price]`;

        container.appendChild(clone);
    }
</script>

</body>
</html>