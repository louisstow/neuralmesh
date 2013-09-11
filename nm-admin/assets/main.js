function toggle(id,link) {
	var obj = document.getElementById(id);
	if(obj.style.display == "none") {
		obj.style.display = "block";
		link.innerHTML = "-";
	} else {
		obj.style.display = "none";
		link.innerHTML = "+";	
	}
}