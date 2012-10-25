function load_add_items_form(){
	var item_type = $("select[name='item_type']").val();
	if(item_type != ""){
		$("#additemform").load("/items/ajax/add_item_form.php?item_type=" + item_type);
	}
}
