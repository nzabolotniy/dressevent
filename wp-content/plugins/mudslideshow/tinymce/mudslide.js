function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}


function insertMudSlideLink() {

	var tagtext;
	var add_text = false;

	var gallery = document.getElementById('gallery_panel');
	var simplegallery = document.getElementById('simplegallery_panel');
	var singlepic = document.getElementById('singlepic_panel');
	var featurepic = document.getElementById('featurepic_panel');
	var user = document.getElementById('user_name1').value;
	var source = document.getElementById('sourcetype1').value;
	
	var type = '';
	switch(source) {
		case '1':
			type = 'picasa';
			break;
		case '2':
			type = 'flickr';
			break;
	}

	// who is active ?
	if (gallery.className.indexOf('current') != -1) {
		var galleryid = document.getElementById('gallerytag').value;
		var config = 0;
		if(document.getElementById('gallery_opt1').checked) config = config + 1;
		if(document.getElementById('gallery_opt2').checked) config = config + 2;
		
		if (galleryid != 0 ) {
			tagtext = "[mudslide:" + type + "," + config + "," + user + "," + galleryid + "]";
			add_text = true;
		}
	}

	if (simplegallery.className.indexOf('current') != -1) {
		var simplegalleryid = document.getElementById('simplegallerytag').value;
		var simplegallerywidth = document.getElementById('simplesize').value;
		var simplegalleryfloat = document.getElementById('simplefloat').value;
		var config = 0;
		if(document.getElementById('simple_opt1').checked) config = config + 1;
		if(document.getElementById('simple_opt2').checked) config = config + 2;

		if (singlepicid != 0 ) {
			tagtext = "[mudslide:" + type + "," + config + "," + user + "," + simplegalleryid + "," + simplegallerywidth + "," + simplegalleryfloat + "]";
			add_text = true;
		}
	}

	if (singlepic.className.indexOf('current') != -1) {
		var gallerypicid = document.getElementById('gallerypictag').value;
		var singlepicid = document.getElementById('singlepictag').value;
		var imgsize = document.getElementById('imgsize').value;
		var imgfloat = document.getElementById('imgfloat').value;
		singlepicid++;
		var config = 0;
		if(document.getElementById('image_opt1').checked) config = config + 1;

		if (singlepicid != 0 ) {
			tagtext = "[mudslide:" + type + "," + config + "," + user + "," + gallerypicid + "," + singlepicid + "," + imgsize + "," + imgfloat + "]";
			add_text = true;
		}
	}
	
	if (featurepic.className.indexOf('current') != -1) {
		var gallerypicid = document.getElementById('galleryfeaturepictag').value;
		var singlepicid = document.getElementById('featurepictag').value;
		singlepicid++;

		if (singlepicid != 0 ) {
			tagtext = "[mudthumb:" + type + "," + user + "," + gallerypicid + "," + singlepicid + "]";
			add_text = true;
		}
	}

	if(add_text) {
		window.tinyMCEPopup.execCommand('mceInsertContent', false, tagtext);
	}
	window.tinyMCEPopup.close();
}
