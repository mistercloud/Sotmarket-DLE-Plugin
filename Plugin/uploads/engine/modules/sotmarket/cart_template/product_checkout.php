<div id="prod_<?= $id ?>" class="product_info_checkout">
	<div class="checkout_left">
   		 <span class="quantity">
      		  <span><?= $quantity ?></span>
      			  x
   			 </span>
   		 <span class="productcart">
    		    <?= $title ?>
   		 </span>
    </div> 
    <div class="checkout_right">
    		<span class="price">
       			 <?= $price ?>
   			</span>
    		<span class="remove" onClick="removeProduct(<?= $id ?>)">
       			 <img src="engine/modules/sotmarket/close.png" alt="delete" title="Удалить">
    	    </span>
    </div>
    <div style="clear:both"></div>
</div>