<div id="prod_<?= $id ?>">
    <span class="quantity">
        <span><?= $quantity ?></span>
        x
    </span>
    <span class="productcart">
        <?= $title ?>
    </span>
    <span class="remove" onClick="removeProduct(<?= $id ?>)">
        <img src="engine/modules/sotmarket/close.png" alt="delete">
    </span>
    <span class="price">
        <?= $price ?>
    </span>
</div>