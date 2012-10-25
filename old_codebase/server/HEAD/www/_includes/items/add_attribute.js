function load_add_attribute_form(){
	var item_type = $("select[name='item_type']").val();
	if(item_type != ""){
		$("#addattrform").load("/items/ajax/add_attribute_form.php?item_type=" + item_type);
	}
}
