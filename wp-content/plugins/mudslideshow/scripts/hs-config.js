var hsSimple = {
	align: 'auto',
	outlineType: null,
	allowSizeReduction: true,
	wrapperClassName: 'in-page controls-hs-simple', //controls-hs-simple is required to enable the thumbnail system
	numberPosition: 'caption',
	useBox: true
};

var hsInfo = {
	captionOverlay: {
		position: 'rightpanel',
		width: '320px'
	}
};

var hsGallery = {
	allowSizeReduction: true,
	numberPosition: 'caption'
};

var hsSingle = {
	allowSizeReduction: true
};


function hsConfig(ref) {
        
  hs.graphicsDir = ref; 
  hs.showCredits = true;
  hs.expandCursor = null;
  hs.restoreCursor = null;
  hs.align = 'center';
  hs.transitions = ['fade', 'crossfade'];
  hs.outlineType = 'drop-shadow';
  hs.fadeInOut = true;
  hs.numberPosition = null;
  hs.captionEval = 'this.a.title';
  
  // Add the controlbar
  hs.addSlideshow({
    interval: 5000,
    repeat: false,
    useControls: true,
    overlayOptions: {
      position: 'bottom center',
      hideOnMouseOut: true,
      opacity: 0.75
    },
    thumbstrip: {
    	position: 'above',
    	mode: 'horizontal',
    	relativeTo: 'expander'
	}
  });
};
