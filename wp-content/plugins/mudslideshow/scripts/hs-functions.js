//Semaphore class
function hs_Semaphore() {
	var me = this; //Just to use me instead of this if a recursived function is used
	
	var status = true; //The green or red light
	me.using = 0;

	//Function to set green
	me.setGreen = function() { status = true; };

	//Is green?
	me.isGreen = function() { return status; };

	//Function to set red
	me.setRed = function() { status = false; };

	//Is red?
	me.isRed = function() { return !status; };
	
}

hs_sem = new hs_Semaphore();
hs_sem.setRed();

hs.Expander.prototype.onAfterExpand = function() {
	hs_sem.setGreen();
}

// Don't display Loading image if it's the first time to display the simple gallery
hs.Expander.prototype.onShowLoading = function() {
	if (/in-page/.test(this.wrapper.className) && hs_sem.isRed()) return false;
	else return true;
}

// Cancel the default action for image click and do next instead
hs.Expander.prototype.onImageClick = function() {
	return hs.next();
}
 
// Under no circumstances should the static popup be closed
hs.Expander.prototype.onBeforeClose = function() {
	if (/in-page/.test(this.wrapper.className))	return false;
}

// Restore the loadingOpacity
hs.Expander.prototype.onInit = function(sender) {
	if (/in-page/.test(sender['wrapperClassName']))	{
		//hs.updateAnchors();
	}
	return true;
}

// ... nor dragged
hs.Expander.prototype.onDrag = function() {
	if (/in-page/.test(this.wrapper.className))	return false;
}

// Keep the position after window resize
hs.addEventListener(window, 'resize', function() {
	var i, exp;
	hs.getPageSize();
 
	for (i = 0; i < hs.expanders.length; i++) {
		exp = hs.expanders[i];
		if (exp) {
			var x = exp.x,
				y = exp.y;
 
			// get new thumb positions
			exp.tpos = hs.getPosition(exp.el);
			x.calcThumb();
			y.calcThumb();
 
			// calculate new popup position
		 	x.pos = x.tpos - x.cb + x.tb;
			x.scroll = hs.page.scrollLeft;
			x.clientSize = hs.page.width;
			y.pos = y.tpos - y.cb + y.tb;
			y.scroll = hs.page.scrollTop;
			y.clientSize = hs.page.height;
			exp.justify(x, true);
			exp.justify(y, true);
 
			// set new left and top to wrapper and outline
			exp.moveTo(x.pos, y.pos);
		}
	}
});
