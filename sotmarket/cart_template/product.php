<div id="prod_<?= $id ?>" class="product_info">
    <span class="quantity">
        <span><?= $quantity ?></span>
        x
    </span>
    <span class="productcart">
        <?= $title ?>
    </span>
    <span class="price">
        <?= $price ?>
    </span>
    <span class="remove" onClick="removeProduct(<?= $id ?>)">
        <img src="engine/modules/sotmarket/close.png" alt="delete" title="Удалить">
    </span>
</div>