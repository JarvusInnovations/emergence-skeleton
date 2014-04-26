Ext.define('Jarvus.ux.Slider', {
    extend: 'Ext.util.Observable',
    
    config: {
        autoPlay: true,
        playDelay: 3000,
        transitionLength: 250,
        slideSelector: '.slide',
        slideParentEl: 'slider', // id as string, or direct reference to a node
        navSelector: '.nav',
        useBullets: true,
        bulletSpec: { tag: 'li', cls: 'bullet' },
        direction: 'horizontal',
        offscreenCls: 'offscreen',
        initialSlide: 0
    },
        
    constructor: function(config) {
        this.initConfig(config);
        this.addEvents('beforeslidechange','slidechange');
        this.callParent(arguments);
        
        this.currentSlide = null;
        this.slideOffset = 0;

        // get ourselves acquainted with the DOM
        this.slideParentEl  = Ext.get(this.slideParentEl);
        this.slides         = Ext.select(this.slideSelector, true, this.slideParentEl.dom);
        this.numSlides      = this.slides.getCount();
        this.slideHeight    = this.slides.item(0).getHeight();
        this.slideWidth     = this.slides.item(0).getWidth();
        
        if (this.useBullets) {
            this.navEl      = this.slideParentEl.down(this.navSelector);
        }
        
        // set styles
        this.slideParentEl.setStyle('overflow', 'hidden');
        
        // wrap slides in a container
        this.slideParentWidth = this.slideParentEl.getWidth();
        this.slidesCt   = Ext.core.DomHelper.append(this.slideParentEl, { cls: 'slides-ct' }, true);
        this.slides.appendTo(this.slidesCt);
        this.slidesCt.setStyle({
            'overflow': 'hidden',
            'white-space': 'nowrap'
        });
        this.slides.setStyle({
            'white-space': 'normal'
            ,'float': 'left'
            ,'display': 'block'
        });
        this.slides.addCls(this.offscreenCls);
        this.slides.setWidth(this.slideParentWidth);
        
        if (this.direction == 'vertical') this.slidesCt.setHeight(this.slideHeight * this.numSlides);
        else this.slidesCt.setWidth(this.slideWidth * this.numSlides);
        
        // feature detection and setup
        this.transition3dSupport = (Modernizr.csstransforms3d && Modernizr.csstransitions);
        this.transition2dSupport = (Modernizr.csstransforms && Modernizr.csstransitions);
        this.transitionProp = Modernizr.prefixed('transition');
        this.transformProp = Modernizr.prefixed('transform');
        
        if(this.transformProp)
            this.cssTransformProp = this.transformProp.replace(/([A-Z])/g, function(str,m1){ return '-' + m1.toLowerCase(); }).replace(/^ms-/,'-ms-');
        
        // use what we discovered to decide how to move the slides around
        this.offsetMode = Modernizr.csstransitions ? this.transformProp : 'margin-left';
        
        // set up the transition CSS
        this.slidesCt.setStyle(this.transitionProp, (this.cssTransformProp + ' ' + this.transitionLength + 'ms'));
        
        // show initial slide
        this.showSlide(this.initialSlide);
        
        // build navigation bullets if useBullets is set
        if (this.useBullets) {
            for (i=0; i<this.numSlides; i++)
            this.navEl.createChild(this.bulletSpec);
            this.bullets = Ext.select('.' + this.bulletSpec.cls);
            
            this.navEl.on('click', function(ev, t){
                var bulletClickedIndex = this.bullets.indexOf(t),
                    bulletClicked = this.bullets.item(bulletClickedIndex);
                
                this.showSlide(bulletClickedIndex);
                this.slideshow.stop();
            }, this, { delegate: '.' + this.bulletSpec.cls });
        }
        
        // set up autoPlay task
        this.slideshow = Ext.create('Ext.util.TaskRunner');
        if (this.autoPlay) this.slideshow.start({
            run: this.nextSlide,
            interval: this.playDelay,
            scope: this
        });
    },
    
    showSlide: function(slide) {
        var previousSlide = this.currentSlide;
        
        if(false === this.fireEvent('beforeslidechange', slide, previousSlide))
            return false;
        
        this.currentSlide = slide;
        var slideOffset;
        
        if (this.direction == 'vertical') slideOffset = this.currentSlide * this.slideHeight;
        else slideOffset = this.currentSlide * this.slideWidth;

        var offsetPixels = (-slideOffset) + 'px';
        var offsetValue;
        
        // construct css rule, based on browser capabilities, to move the slide container
        if (this.transition3dSupport) {
            if (this.direction == 'horizontal') offsetValue = 'translate3d(' + offsetPixels + ',0,0)';
            else offsetValue = 'translate3d(0,' + offsetPixels + ',0)';
        } else if (this.transition2dSupport) {
            if (this.direction == 'horizontal') offsetValue = 'translateX(' + offsetPixels + ')';
            else offsetValue = 'translateY(' + offsetPixels + ')';
        } else {
            offsetValue = offsetPixels;
        }

        // on screen
        this.slides.item(slide).removeCls(this.offscreenCls);
        
        // apply style shift
        this.slidesCt.setStyle(this.offsetMode, offsetValue);
        
        // off screen
        if(previousSlide !== null)
        {       
            Ext.defer(function(slideEl) {
                slideEl.addCls(this.offscreenCls);
            }, this.transitionLength, this, [this.slides.item(previousSlide)]);
        }
        
        // select bullet
        if (this.useBullets) this.bullets.item(slide).radioCls('current');
        
        
        this.fireEvent('slidechange', slide, previousSlide)
    },
    
    nextSlide: function() {
        if (this.currentSlide < (this.numSlides-1))
            this.showSlide(this.currentSlide+1);
        else
            this.showSlide(0);
    },
    
    prevSlide: function() {
        if (this.currentSlide > 0)
            this.showSlide(this.currentSlide-1);
        else
            this.showSlide(this.numSlides-1);      
    },
});