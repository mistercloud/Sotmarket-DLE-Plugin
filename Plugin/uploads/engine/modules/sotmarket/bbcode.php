<?php

$sTemplates = '';
$sSotmarketTemplatesPath = ROOT_DIR  .'/templates/' . $config['skin'] . '/sotmarket/';
$aTemplateFiles = glob($sSotmarketTemplatesPath.'*.tpl');
$aTemplateFiles = str_replace( array($sSotmarketTemplatesPath,'.tpl'),'',$aTemplateFiles );

foreach($aTemplateFiles as $sTemplate){
    $sTemplates .= '<option value="'.$sTemplate.'" >'.$sTemplate.'</option>';
}


$sSotmarketBB = <<<HTML

<SCRIPT type=text/javascript>
    function sotmarket_tag(forWisiwig , ed)
    {
        $( "#sotmarket_dialog" ).dialog({
            modal: true,
            buttons: {
                "Вставить тег": function() {
                    var code = '';
                    code += '{include file="engine/modules/sotmarket/sotmarket.php?';
                    code += 'type='+$('#sotmarket-type').val();
                    code += '&';
                    code += 'product_id='+$('#sotmarket-product-id').val();
                    code += '&';
                    code += 'product_name='+$('#sotmarket-product-name').val();
                    code += '&';
                    code += 'cnt='+$('#sotmarket-cnt').val();
                    code += '&';
                    code += 'image_size='+$('#sotmarket-image-size').val();
                    code += '&';
                    code += 'template='+$('#sotmarket-template').val();
                    code += '&';
                    code += 'fullstory='+$('#sotmarket-fullstory').val();
                    code += '&';
				    code += 'categories='+$('#sotmarket-cats').val();
				    code += '&';
				    code += 'subref='+$('#sotmarket-subref').val();
                    //code += '&';

                    code += '"}';

                    if (forWisiwig == 1 ){


                        var oEditor = oUtil.oEditor;
	                    var obj = oUtil.obj;

                        obj.saveForUndo();
                        oEditor.focus();
                        obj.setFocus();
                        var txt = obj.getXHTMLBody();
                        txt = txt + code;
	                    obj.loadHTML(txt);
                    } else if (forWisiwig == 2){
                        //$('#file_text').html(code);
                        //editor.setLine(2, code);
                        var line = editor.getCursor().line;
                        var text = editor.getLine(line);
                        editor.setLine(line, text+code);
                        //editor.indentLine(code);
                    } else if (forWisiwig == 3) {
						ed.execCommand('mceInsertContent',false,code);
                    } else {
                        doInsert(code, "",false);
                    }

                    $( this ).dialog( "close" );
                }
            }

        });



    }
</SCRIPT>
<div id="sotmarket_dialog" title="Генерация тега Сотмаркет" style="display: none;">
    <p>Генерация тега</p>
    <p>
	    <label>Тип тега</label><br/>
		<select id="sotmarket-type">
		    <option value="products" selected>Товары</option>
			<option value="related">Аксесуары</option>
			<option value="analog">Похожие товары</option>
		</select>
	</p>
	<p>
		<label>ID товара (товаров через запятую):</label><br/>
		<input type="text" id="sotmarket-product-id" value="" />
	</p>
	<p>
		<label>Название товара:</label><br/>
		<input type="text" id="sotmarket-product-name" value="" />
	</p>
    <p>
		<label>Сколько товаров выводить:</label><br/>
		<input type="text" id="sotmarket-cnt" value="1" />
	</p>
	<p>
		<label>Размер картинки:</label><br/>
		<select id="sotmarket-image-size">
            <option value="default" >стандартные</option>
            <option value="100x100">100x100</option>
            <option value="140x200">140x200</option>
            <option value="1200x1200">1200x1200</option>
            <option value="100x150">100x150</option>
            <option value="50x50">50x50</option>
        </select>
	</p>
	<p>
		<label>Шаблон:</label><br/>
		<select id="sotmarket-template">
            $sTemplates
		</select>
	</p>
	<p>
		<label>ID категории (категорий через запятую):</label><br/>
		<input type="text" id="sotmarket-cats" value="" />
	</p>
	<p>
		<label>Брать id или имя из дополнительного поля:</label><br/>
		<select id="sotmarket-fullstory">
            <option value="off" >Нет</option>
            <option value="on">Да</option>
        </select>
	</p>
	<p>
		<label>Значение subref:</label><br/>
		<input type="text" id="sotmarket-subref" value="" />
	</p>
</div>

HTML;


