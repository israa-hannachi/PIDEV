/**
* naja7ni - LMS, Education and Course Theme
*
* @author Webestica (https://www.webestica.com/)
* @version 1.4.1
**/


/* ===================
Table Of Content
======================
01 PRELOADER
02 MEGA MENU
03 STICKY HEADER
04 TINY SLIDER
05 STICKY BAR
06 TOOLTIP
07 POPOVER
08 BACK TO TOP
09 GLIGHTBOX
10 ISOTOPE
11 CHOICES
12 AOS ANIMATION
13 DASHBOARD CHART
14 EARNING CHART
15 EARNING CHART 2
16 TRAFFIC CHART
17 ACTIVE STUDENT CHART
18 ACTIVE STUDENT CHART 2
19 REVIEW CHART
20 QUILL EDITOR
21 STEPPER
22 VIDEO PLAYER
23 PRICING
24 STICKY ELEMENT
24 OVERLAY SCROLLBARS
26 FLATPICKER
====================== */

"use strict";
!function () {

    window.Element.prototype.removeClass = function () {
        let className = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
            selectors = this;
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (this.isVariableDefined(selectors) && className) {
            selectors.classList.remove(className);
        }
        return this;
    }, window.Element.prototype.addClass = function () {
        let className = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
            selectors = this;
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (this.isVariableDefined(selectors) && className) {
            selectors.classList.add(className);
        }
        return this;
    }, window.Element.prototype.toggleClass = function () {
        let className = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
            selectors = this;
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (this.isVariableDefined(selectors) && className) {
            selectors.classList.toggle(className);
        }
        return this;
    }, window.Element.prototype.isVariableDefined = function () {
        return !!this && typeof (this) != 'undefined' && this != null;
    }
}();

// Get CSS var value
var ThemeColor = function () {
  return {
    getCssVariableValue: function (e) {
      var t = getComputedStyle(document.documentElement).getPropertyValue(e);
      return t && t.length > 0 && (t = t.trim()), t;
    }
  };
}();

var e = {
    init: function () {
        e.preLoader(),
        e.megaMenu(),
        e.stickyHeader(),
        e.tinySlider(),
        e.stickyBar(),
        e.toolTipFunc(),
        e.popOverFunc(),
        e.backTotop(),
        e.lightBox(),
        e.enableIsotope(),
        e.choicesSelect(),
        e.aosFunc(),
        e.dashboardChart(),
        e.earningChart(),
        e.earningChart2(),
        e.trafficChart(),
        e.activeChart(),
        e.activeChart2(),
        e.reviewChart(),
        e.quill(),
        e.stepper(),
        e.videoPlyr(),
        e.pricing(),
        e.stickyElement(),
        e.overlayScrollbars(),
        e.flatPicker();
        
    },
    isVariableDefined: function (el) {
        return typeof !!el && (el) != 'undefined' && el != null;
    },
    getParents: function (el, selector, filter) {
        const result = [];
        const matchesSelector = el.matches || el.webkitMatchesSelector || el.mozMatchesSelector || el.msMatchesSelector;

        // match start from parent
        el = el.parentElement;
        while (el && !matchesSelector.call(el, selector)) {
            if (!filter) {
                if (selector) {
                    if (matchesSelector.call(el, selector)) {
                        return result.push(el);
                    }
                } else {
                    result.push(el);
                }
            } else {
                if (matchesSelector.call(el, filter)) {
                    result.push(el);
                }
            }
            el = el.parentElement;
            if (e.isVariableDefined(el)) {
                if (matchesSelector.call(el, selector)) {
                    return el;
                }
            }

        }
        return result;
    },
    getNextSiblings: function (el, selector, filter) {
        let sibs = [];
        let nextElem = el.parentNode.firstChild;
        const matchesSelector = el.matches || el.webkitMatchesSelector || el.mozMatchesSelector || el.msMatchesSelector;
        do {
            if (nextElem.nodeType === 3) continue; // ignore text nodes
            if (nextElem === el) continue; // ignore elem of target
            if (nextElem === el.nextElementSibling) {
                if ((!filter || filter(el))) {
                    if (selector) {
                        if (matchesSelector.call(nextElem, selector)) {
                            return nextElem;
                        }
                    } else {
                        sibs.push(nextElem);
                    }
                    el = nextElem;

                }
            }
        } while (nextElem = nextElem.nextSibling)
        return sibs;
    },
    on: function (selectors, type, listener) {
        document.addEventListener("DOMContentLoaded", () => {
            if (!(selectors instanceof HTMLElement) && selectors !== null) {
                selectors = document.querySelector(selectors);
            }
            selectors.addEventListener(type, listener);
        });
    },
    onAll: function (selectors, type, listener) {
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(selectors).forEach((element) => {
                if (type.indexOf(',') > -1) {
                    let types = type.split(',');
                    types.forEach((type) => {
                        element.addEventListener(type, listener);
                    });
                } else {
                    element.addEventListener(type, listener);
                }


            });
        });
    },
    removeClass: function (selectors, className) {
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (e.isVariableDefined(selectors)) {
            selectors.removeClass(className);
        }
    },
    removeAllClass: function (selectors, className) {
        if (e.isVariableDefined(selectors) && (selectors instanceof HTMLElement)) {
            document.querySelectorAll(selectors).forEach((element) => {
                element.removeClass(className);
            });
        }

    },
    toggleClass: function (selectors, className) {
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (e.isVariableDefined(selectors)) {
            selectors.toggleClass(className);
        }
    },
    toggleAllClass: function (selectors, className) {
        if (e.isVariableDefined(selectors)  && (selectors instanceof HTMLElement)) {
            document.querySelectorAll(selectors).forEach((element) => {
                element.toggleClass(className);
            });
        }
    },
    addClass: function (selectors, className) {
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (e.isVariableDefined(selectors)) {
            selectors.addClass(className);
        }
    },
    select: function (selectors) {
        return document.querySelector(selectors);
    },
    selectAll: function (selectors) {
        return document.querySelectorAll(selectors);
    },

    

    // START: 01 Preloader
    preLoader: function () {
        window.onload = function () {
            var preloader = e.select('.preloader');
            if (e.isVariableDefined(preloader)) {
                preloader.className += ' animate__animated animate__fadeOut';
                setTimeout(function(){
                    preloader.style.display = 'none';
                }, 200);
            }
        };
    },
    // END: Preloader

    // START: 02 Mega Menu
    megaMenu: function () {
        e.onAll('.dropdown-menu a.dropdown-item.dropdown-toggle', 'click', function (event) {
            var element = this;
            event.preventDefault();
            event.stopImmediatePropagation();
            if (e.isVariableDefined(element.nextElementSibling) && !element.nextElementSibling.classList.contains("show")) {
                const parents = e.getParents(element, '.dropdown-menu');
                e.removeClass(parents.querySelector('.show'), "show");
                if(e.isVariableDefined(parents.querySelector('.dropdown-opened'))){
                    e.removeClass(parents.querySelector('.dropdown-opened'), "dropdown-opened");
                }

            }
            var $subMenu = e.getNextSiblings(element, ".dropdown-menu");
            e.toggleClass($subMenu, "show");
            $subMenu.previousElementSibling.toggleClass('dropdown-opened');
            var parents = e.getParents(element, 'li.nav-item.dropdown.show');
            if (e.isVariableDefined(parents) && parents.length > 0) {
                e.on(parents, 'hidden.bs.dropdown', function (event) {
                    e.removeAllClass('.dropdown-submenu .show');
                });
            }
        });
    },
    // END: Mega Menu

    // START: 03 Sticky Header
    stickyHeader: function () {
        var stickyNav = e.select('.navbar-sticky');
        if (e.isVariableDefined(stickyNav)) {
            var stickyHeight = stickyNav.offsetHeight;
            stickyNav.insertAdjacentHTML('afterend', '<div id="sticky-space"></div>');
            var stickySpace = e.select('#sticky-space');
            if (e.isVariableDefined(stickySpace)) {
                document.addEventListener('scroll', function (event) {
                    var scTop = window.pageYOffset || document.documentElement.scrollTop;
                    if (scTop >= 400) {
                        stickySpace.addClass('active');
                        e.select("#sticky-space.active").style.height = stickyHeight + 'px';
                        stickyNav.addClass('navbar-sticky-on');
                    } else {
                        stickySpace.removeClass('active');
                        stickySpace.style.height = '0px';
                        stickyNav.removeClass("navbar-sticky-on");
                    }
                });
            }
        }
    },
    // END: Sticky Header

    // START: 04 Tiny Slider
    tinySlider: function () {
        var $carousel = e.select('.tiny-slider-inner');
        if (e.isVariableDefined($carousel)) {
          var tnsCarousel = e.selectAll('.tiny-slider-inner');
          tnsCarousel.forEach(slider => {
              var slider1 = slider;
              var sliderMode = slider1.getAttribute('data-mode') ? slider1.getAttribute('data-mode') : 'carousel';
              var sliderAxis = slider1.getAttribute('data-axis') ? slider1.getAttribute('data-axis') : 'horizontal';
              var sliderSpace = slider1.getAttribute('data-gutter') ? slider1.getAttribute('data-gutter') : 30;
              var sliderEdge = slider1.getAttribute('data-edge') ? slider1.getAttribute('data-edge') : 0;

              var sliderItems = slider1.getAttribute('data-items') ? slider1.getAttribute('data-items') : 4; //option: number (items in all device)
              var sliderItemsXl = slider1.getAttribute('data-items-xl') ? slider1.getAttribute('data-items-xl') : Number(sliderItems); //option: number (items in 1200 to end )
              var sliderItemsLg = slider1.getAttribute('data-items-lg') ? slider1.getAttribute('data-items-lg') : Number(sliderItemsXl); //option: number (items in 992 to 1199 )
              var sliderItemsMd = slider1.getAttribute('data-items-md') ? slider1.getAttribute('data-items-md') : Number(sliderItemsLg); //option: number (items in 768 to 991 )
              var sliderItemsSm = slider1.getAttribute('data-items-sm') ? slider1.getAttribute('data-items-sm') : Number(sliderItemsMd); //option: number (items in 576 to 767 )
              var sliderItemsXs = slider1.getAttribute('data-items-xs') ? slider1.getAttribute('data-items-xs') : Number(sliderItemsSm); //option: number (items in start to 575 )

              var sliderSpeed = slider1.getAttribute('data-speed') ? slider1.getAttribute('data-speed') : 500;
              var sliderautoWidth = slider1.getAttribute('data-autowidth') === 'true'; //option: true or false
              var sliderArrow = slider1.getAttribute('data-arrow') !== 'false'; //option: true or false
              var sliderDots = slider1.getAttribute('data-dots') !== 'false'; //option: true or false

              var sliderAutoPlay = slider1.getAttribute('data-autoplay') !== 'false'; //option: true or false
              var sliderAutoPlayTime = slider1.getAttribute('data-autoplaytime') ? slider1.getAttribute('data-autoplaytime') : 4000;
              var sliderHoverPause = slider1.getAttribute('data-hoverpause') === 'true'; //option: true or false
              if (e.isVariableDefined(e.select('.custom-thumb'))) {
                var sliderNavContainer = e.select('.custom-thumb');
              } 
              var sliderLoop = slider1.getAttribute('data-loop') !== 'false'; //option: true or false
              var sliderRewind = slider1.getAttribute('data-rewind') === 'true'; //option: true or false
              var sliderAutoHeight = slider1.getAttribute('data-autoheight') === 'true'; //option: true or false
              var sliderfixedWidth = slider1.getAttribute('data-fixedwidth') === 'true'; //option: true or false
              var sliderTouch = slider1.getAttribute('data-touch') !== 'false'; //option: true or false
              var sliderDrag = slider1.getAttribute('data-drag') !== 'false'; //option: true or false
              // Check if document DIR is RTL
              var ifRtl = document.getElementsByTagName("html")[0].getAttribute("dir");
              var sliderDirection;
              if (ifRtl === 'rtl') {
                  sliderDirection = 'rtl';
              }

              var tnsSlider = tns({
                  container: slider,
                  mode: sliderMode,
                  axis: sliderAxis,
                  gutter: sliderSpace,
                  edgePadding: sliderEdge,
                  speed: sliderSpeed,
                  autoWidth: sliderautoWidth,
                  controls: sliderArrow,
                  nav: sliderDots,
                  autoplay: sliderAutoPlay,
                  autoplayTimeout: sliderAutoPlayTime,
                  autoplayHoverPause: sliderHoverPause,
                  autoplayButton: false,
                  autoplayButtonOutput: false,
                  controlsPosition: top,
                  navContainer: sliderNavContainer,
                  navPosition: top,
                  autoplayPosition: top,
                  controlsText: [
                      '<i class="fas fa-chevron-left"></i>',
                      '<i class="fas fa-chevron-right"></i>'
                  ],
                  loop: sliderLoop,
                  rewind: sliderRewind,
                  autoHeight: sliderAutoHeight,
                  fixedWidth: sliderfixedWidth,
                  touch: sliderTouch,
                  mouseDrag: sliderDrag,
                  arrowKeys: true,
                  items: sliderItems,
                  textDirection: sliderDirection,
                  responsive: {
                      0: {
                          items: Number(sliderItemsXs)
                      },
                      576: {
                          items: Number(sliderItemsSm)
                      },
                      768: {
                          items: Number(sliderItemsMd)
                      },
                      992: {
                          items: Number(sliderItemsLg)
                      },
                      1200: {
                          items: Number(sliderItemsXl)
                      }
                  }
              });
          }); 
        }
    },
    // END: Tiny Slider

    // START: 05 Sticky Bar
    stickyBar: function () {
        var sb = e.select('[data-sticky]');
        if (e.isVariableDefined(sb)) {
            var sticky = new Sticky('[data-sticky]');
        }
    },
    // END: Sticky Bar

    // START: 06 Tooltip
    // Enable tooltips everywhere via data-toggle attribute
    toolTipFunc: function () {
        var tooltipTriggerList = [].slice.call(e.selectAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    },
    // END: Tooltip

    // START: 07 Popover
    // Enable popover everywhere via data-toggle attribute
    popOverFunc: function () {
        var popoverTriggerList = [].slice.call(e.selectAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
          return new bootstrap.Popover(popoverTriggerEl)
        })
    },
    // END: Popover

    // START: 08 Back to Top
    backTotop: function () {
        var scrollpos = window.scrollY;
        var backBtn = e.select('.back-top');
        if (e.isVariableDefined(backBtn)) {
            var add_class_on_scroll = () => backBtn.addClass("back-top-show");
            var remove_class_on_scroll = () => backBtn.removeClass("back-top-show");

            window.addEventListener('scroll', function () {
                scrollpos = window.scrollY;
                if (scrollpos >= 800) {
                    add_class_on_scroll()
                } else {
                    remove_class_on_scroll()
                }
            });

            backBtn.addEventListener('click', () => window.scrollTo({
                top: 0,
                behavior: 'smooth',
            }));
        }
    },
    // END: Back to Top

    // START: 09 GLightbox
    lightBox: function () {
        var light = e.select('[data-glightbox]');
        if (e.isVariableDefined(light)) {
            var lb = GLightbox({
                selector: '*[data-glightbox]',
                openEffect: 'fade',
                closeEffect: 'fade'
            });
        }
    },
    // END: GLightbox

    // START: 10 Isotope
    enableIsotope: function () {
        var isGridItem = e.select('.grid-item');
        if (e.isVariableDefined(isGridItem)) {

            // Code only for normal Grid
            var onlyGrid = e.select('[data-isotope]');
            if (e.isVariableDefined(onlyGrid)) {
                var allGrid = e.selectAll("[data-isotope]");
                allGrid.forEach(gridItem => {
                    var gridItemData = gridItem.getAttribute('data-isotope');
                    var gridItemDataObj = JSON.parse(gridItemData);
                    var iso = new Isotope(gridItem, {
                        itemSelector: '.grid-item',
                        layoutMode: gridItemDataObj.layoutMode
                    });

                    imagesLoaded(gridItem).on('progress', function () {
                        // layout Isotope after each image loads
                        iso.layout();
                    });
                });
            }

            // Code only for normal Grid
            var onlyGridFilter = e.select('.grid-menu');
            if (e.isVariableDefined(onlyGridFilter)) {
                var filterMenu = e.selectAll('.grid-menu');
                filterMenu.forEach(menu => {
                    var filterContainer = menu.getAttribute('data-target');
                    var a = menu.dataset.target;
                    var b = e.select(a);
                    var filterContainerItemData = b.getAttribute('data-isotope');
                    var filterContainerItemDataObj = JSON.parse(filterContainerItemData);
                    var filter = new Isotope(filterContainer, {
                        itemSelector: '.grid-item',
                        transitionDuration: '0.7s',
                        layoutMode: filterContainerItemDataObj.layoutMode
                    });

                    var menuItems = menu.querySelectorAll('li a');
                    menuItems.forEach(menuItem => {
                        menuItem.addEventListener('click', function (event) {
                            var filterValue = menuItem.getAttribute('data-filter');
                            filter.arrange({filter: filterValue});
                            menuItems.forEach((control) => control.removeClass('active'));
                            menuItem.addClass('active');
                        });
                    });

                    imagesLoaded(filterContainer).on('progress', function () {
                        filter.layout();
                    });
                });
            }

        }
    },
    // END: Isotope

    // START: 11 Choices
    choicesSelect: function () {
       var choice = e.select('.js-choice');
       
       if (e.isVariableDefined(choice)) {
         var element = document.querySelectorAll('.js-choice');

         element.forEach(function (item) {
           var removeItemBtn = item.getAttribute('data-remove-item-button') == 'true' ? true : false;
           var placeHolder = item.getAttribute('data-placeholder') == 'false' ? false : true;
           var placeHolderVal = item.getAttribute('data-placeholder-val') ? item.getAttribute('data-placeholder-val') : 'Type and hit enter';
           var maxItemCount = item.getAttribute('data-max-item-count') ? item.getAttribute('data-max-item-count') : 3;
           var searchEnabled = item.getAttribute('data-search-enabled') == 'false' ? false : true;

           var choices = new Choices(item, {
               removeItemButton: removeItemBtn,
               placeholder: placeHolder,
               placeholderValue: placeHolderVal,
               maxItemCount: maxItemCount,
               searchEnabled: searchEnabled
           });

         });
       }
    },
    // END: Choices

    // START: 12 AOS Animation
    aosFunc: function () {
        var aos = e.select('.aos');
        if (e.isVariableDefined(aos)) {
            AOS.init({
                duration: 500,
                easing: 'ease-out-quart',
                once: true
            });
        }
    },
    // END: AOS Animation

    // START: 13 Dashboard Chart
    dashboardChart: function () {
        var ac = e.select('#ChartPayout');
        if (e.isVariableDefined(ac)) {
        var options = {
          series: [{
            name: 'Payout',
            data: [2909, 1259, 950, 1563, 1825, 2526, 2010, 3260, 3005, 3860, 4039]
          }],
          chart: {
            height: 300,
            type: 'area',
            toolbar: {
              show: false
            },
          },
          
          dataLabels: {
            enabled: true
          },
          stroke: {
            curve: 'smooth'
          },
          colors: [ThemeColor.getCssVariableValue('--bs-primary')],
          xaxis: {
            type: 'Payout',
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct ', 'Nov', 'Dec'],
            axisBorder: {
               show: false
            },
            axisTicks: {
                show: false
            },
          },
          yaxis: [{
              axisTicks: {
                show: false
              },
              axisBorder: {
                show: false
              },
            }],
          tooltip: {
            y: {
              title: {
                formatter: function (e) {
                  return "" + "$";
                }
              }
            },
            marker: {
              show: !1
            }
          }
        };
        var chart = new ApexCharts(document.querySelector("#ChartPayout"), options);
        chart.render();
        }
    },
    // END: Dashboard Chart

    // START: 14 Earning Chart
    earningChart: function () {
        var cpe = e.select('#ChartPayoutEarning');
        if (e.isVariableDefined(cpe)) {
        var options = {
          series: [{
            name: 'Payout',
            data: [500, 700, 900, 1500, 1800, 1000, 0, 2000, 3200, 3000, 4800, 4000]
          }],
          chart: {
            height: 300,
            type: 'area',
            sparkline: {
              enabled: !0
            }
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            curve: 'smooth'
          },
          colors: [ThemeColor.getCssVariableValue('--bs-primary')],
          xaxis: {
            type: 'Payout',
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct ', 'Nov', 'Dec']
          },
          grid: {
            
          },
          tooltip: {
            y: {
              title: {
                formatter: function (e) {
                  return "" + "$";
                }
              }
            },
            marker: {
              show: !1
            }
          }
        };
        var chart = new ApexCharts(document.querySelector("#ChartPayoutEarning"), options);
        chart.render();
        }
    },
    // END: Earning Chart

    // START: 15 Earning Chart 2
    earningChart2: function () {
        var cpv = e.select('#ChartPageViews');
        if (e.isVariableDefined(cpv)) {
          // CHART: Page Views
          var options = {
            series: [50, 20, 20, 10, 10],
            labels: ['Course-1', 'Course-2', 'Course-3', 'Course-4', 'Course-5'],
            chart: {
              height: 300,
              width: 300,
              offsetX: 50,
              type: 'donut',
              sparkline: {
                enabled: !0
              }
            },
            colors: [
              ThemeColor.getCssVariableValue('--bs-success'),
              ThemeColor.getCssVariableValue('--bs-warning'),
              ThemeColor.getCssVariableValue('--bs-danger'),
              ThemeColor.getCssVariableValue('--bs-primary'),
              ThemeColor.getCssVariableValue('--bs-secondary')
            ],
            tooltip: {
              theme: "dark"
            },
            responsive: [{
              breakpoint: 480,
              options: {
                chart: {
                  width: 200,
                  height: 200,
                },
                legend: {
                  position: 'bottom'
                }
              }
            }]
          };
          var chart = new ApexCharts(document.querySelector("#ChartPageViews"), options);
          chart.render();
        }
    },
    // END: Earning Chart 2

    // START: 16 Traffic Chart
    trafficChart: function () {
      var cpv = e.select('#ChartTrafficViews');
      if (e.isVariableDefined(cpv)) {
        // CHART: Page Views
        var options = {
          series: [70, 15, 10, 5],
          labels: ['Course-1', 'Course-2', 'Course-3', 'Course-4'],
          chart: {
            height: 200,
            width: 200,
            offsetX: 0,
            type: 'donut',
            sparkline: {
              enabled: !0
            }
          },
          colors: [
            ThemeColor.getCssVariableValue('--bs-primary'),
            ThemeColor.getCssVariableValue('--bs-success'),
            ThemeColor.getCssVariableValue('--bs-warning'),
            ThemeColor.getCssVariableValue('--bs-danger')
          ],
          tooltip: {
            theme: "dark"
          },
          responsive: [{
            breakpoint: 480,
            options: {
              chart: {
                width: 200,
                height: 200,
              },
              legend: {
                position: 'bottom'
              }
            }
          }]
        };
        var chart = new ApexCharts(document.querySelector("#ChartTrafficViews"), options);
        chart.render();
      }
    },
    // END: Traffic Chart

    // START: 17 Active student Chart
    activeChart: function () {
      var jj = document.querySelector("#activeChartstudent");
      if(typeof(jj) != 'undefined' && jj != null){
      var options = {
        series: [{
          name: 'Conversion',
          data: [200, 290, 500, 500, 430, 316, 478, 700]
        }],
        chart: {
          height: 130,
          type: 'area',
          sparkline: {
            enabled: !0
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth'
        },
        colors: [ThemeColor.getCssVariableValue('--bs-success')],
        xaxis: {
          type: 'category',
          categories: ['Dec 01', 'Dec 02', 'Dec 03', 'Dec 04', 'Dec 05', 'Dec 06', 'Dec 07', 'Dec 08', 'Dec 09 ', 'Dec 10', 'Dec 11']
        },
        grid: {
          
        },
        tooltip: {
          y: {
            title: {
              formatter: function (e) {
                return "";
              }
            }
          },
          marker: {
            show: !1
          }
        }
      };
      var chart = new ApexCharts(document.querySelector("#activeChartstudent"), options);
      chart.render();
      }
    },
    // END: Active student Chart

    // START: 18 Active student Chart 2
    activeChart2: function () {
      var jj = document.querySelector("#activeChartstudent2");
      if(typeof(jj) != 'undefined' && jj != null){
      var options = {
        series: [{
          name: 'Conversion',
          data: [200, 290, 325, 500, 600, 316, 478, 700]
        }],
        chart: {
          height: 130,
          type: 'area',
          sparkline: {
            enabled: !0
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth'
        },
        colors: [ThemeColor.getCssVariableValue('--bs-purple')],
        xaxis: {
          type: 'category',
          categories: ['Dec 01', 'Dec 02', 'Dec 03', 'Dec 04', 'Dec 05', 'Dec 06', 'Dec 07', 'Dec 08', 'Dec 09 ', 'Dec 10', 'Dec 11']
        },
        grid: {
          
        },
        tooltip: {
          y: {
            title: {
              formatter: function (e) {
                return "";
              }
            }
          },
          marker: {
            show: !1
          }
        }
      };
      var chart = new ApexCharts(document.querySelector("#activeChartstudent2"), options);
      chart.render();
      }
    },
    // END: Active student Chart 2

    // START: 19 Review chart
    reviewChart: function (){
      var ff = document.querySelector("#apexChartPageViews");
      if(typeof(ff) != 'undefined' && ff != null){
      var options = {
        series: [80, 30],
        labels: ['Positive', 'Negative'],
        chart: {
          height: 300,
          width: 300,
          type: 'donut',
          sparkline: {
            enabled: !0
          }
        },
        stroke: {
          colors: 'transparent',
        },
        colors: [
          ThemeColor.getCssVariableValue('--bs-success'),
          ThemeColor.getCssVariableValue('--bs-danger'),
        ],
        tooltip: {
          theme: "dark"
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              height: 100,
              width: 100
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
      };
      var chart = new ApexCharts(document.querySelector("#apexChartPageViews"), options);
      chart.render();
      }
    },
    // END: Review chart

    // START: 20 Quill Editor
    quill: function () {
        var ql = e.select('#quilleditor');
        if (e.isVariableDefined(ql)) {
          var editor = new Quill('#quilleditor', {
            modules: { toolbar: '#quilltoolbar' },
            theme: 'snow'
          });
        }
    },
    // END: Quill Editor

    // START: 21 Stepper
    stepper: function () {
        var stp = e.select('#stepper');
        if (e.isVariableDefined(stp)) {
          var nxtBtn = document.querySelectorAll('.next-btn');
          var prvBtn = document.querySelectorAll('.prev-btn');

          var stepper = new Stepper(document.querySelector('#stepper'), {
            linear: false,
            animation: true
          });

          nxtBtn.forEach(function (button) {
            button.addEventListener("click", () =>{
            stepper.next()
          })
          });

          prvBtn.forEach(function (button) {
            button.addEventListener("click", () =>{
            stepper.previous()
          })
          });
        }
    },
    // END: Stepper

    // START: 22 Video player
    videoPlyr: function () {
        var vdp = e.select('.video-player');
        if (e.isVariableDefined(vdp)) {
            // youtube
            const playerYoutube = new Plyr('#player-youtube', {});
            window.player = playerYoutube;

            // Vimeo
            const playerVimeo = new Plyr('#player-vimeo', {});
            window.player = playerVimeo;
            
            // HTML video
            const playerHtmlvideo = new Plyr('video', {
                captions: {active: true}
            });
            window.player = playerHtmlvideo;

            // HTML audio
            const playerHtmlaudio = new Plyr('audio', {
                captions: {active: true}
            });
            window.player = playerHtmlaudio;
        }
    },
    // END: Video player

    // START: 23 Pricing
    pricing: function () {
        var p = e.select('.price-wrap');
        if (e.isVariableDefined(p)) {
          var pWrap = e.selectAll(".price-wrap");
          pWrap.forEach(item => {

            var priceSwitch = item.querySelector('.price-toggle'),
            priceElement = item.querySelectorAll('.plan-price');

            priceSwitch.addEventListener('change', function () {
              if (priceSwitch.checked) {
                priceElement.forEach(pItem => {
                  var dd = pItem.getAttribute('data-annual-price');
                  pItem.innerHTML = dd;
                });
              } else {
                priceElement.forEach(pItem => {
                  var ee = pItem.getAttribute('data-monthly-price');
                  pItem.innerHTML = ee;
                });
              }
            });
          });
        }
    },
    // END: Pricing

    // START: 24 Sticky element
    stickyElement: function () {
    var scrollpos = window.scrollY;
    var sp = e.select('.sticky-element');
    if (e.isVariableDefined(sp)) {
        var add_class_on_scroll = () => sp.addClass("sticky-element-sticked");
        var remove_class_on_scroll = () => sp.removeClass("sticky-element-sticked");

        window.addEventListener('scroll', function () {
            scrollpos = window.scrollY;
            if (scrollpos >= 800) {
                add_class_on_scroll()
            } else {
                remove_class_on_scroll()
            }
        });
    }
    },
    // END: Sticky element
 
    // START: 25 Overlay scrollbars
    overlayScrollbars: function () {
      var os = e.select('.custom-scrollbar');
      if (os) {
        document.addEventListener("DOMContentLoaded", function() {
          var cs = document.querySelectorAll('.custom-scrollbar');
          cs.forEach(c => {
              OverlayScrollbars(c, {
                scrollbars: {
                  autoHide: 'leave',
                  autoHideDelay: 200
                },
                overflowBehavior : {
                    x : "visible-hidden",
                    y : "scroll"
                }
               });
          });
        });
      }
    },
    // END: Overlay scrollbars

    // START: 26 Flatpicker
    flatPicker: function () {

      var picker = e.select('.flatpickr');
      if (e.isVariableDefined(picker)) {
        var element = e.selectAll('.flatpickr');
        element.forEach(function (item) {
          var mode = item.getAttribute('data-mode') == 'multiple' ? 'multiple' : item.getAttribute('data-mode') == 'range' ? 'range' : 'single';
          var enableTime = item.getAttribute('data-enableTime') == 'true' ? true : false;
          var noCalendar = item.getAttribute('data-noCalendar') == 'true' ? true : false;
          var inline = item.getAttribute('data-inline') == 'true' ? true : false;
          var dateFormat = item.getAttribute('data-date-format') ? item.getAttribute('data-date-format') : item.getAttribute('data-enableTime') == 'true' ? "h:i K" : "d M";

          flatpickr(item, {
            mode: mode,
            enableTime: enableTime,
            noCalendar: noCalendar,
            inline: inline,
            animate: "false",
            position: "top",
            dateFormat: dateFormat, //Check supported characters here: https://flatpickr.js.org/formatting/
            disableMobile: "true"
          });

        });
      }
    },
    // END: Flatpicker

};
e.init();

// Customizations: hide Demos & profile defaults and inject Sign In / Sign Up into header
document.addEventListener('DOMContentLoaded', function () {
  // hide Demos and profile if present (extra safety)
  var demo = document.getElementById('demoMenu');
  if (demo) { demo.style.display = 'none'; var dm = demo.nextElementSibling; if (dm) dm.style.display = 'none'; }
  var profile = document.getElementById('profileDropdown');
  if (profile) { var parent = profile.closest('.dropdown'); if (parent) parent.style.display = 'none'; }
  // hide three-dots icons
  document.querySelectorAll('.bi-three-dots, .bi-three-dots-vertical').forEach(function(el){ el.style.display = 'none'; });
  // inject Sign In / Sign Up links in header nav (if not present)
  var navs = document.querySelectorAll('.navbar-collapse .navbar-nav');
  if (navs && navs.length > 0) {
    var nav = navs[navs.length - 1]; // right side nav
    if (!document.querySelector('a[href="sign-in.html"]')) {
      var li = document.createElement('li'); li.className = 'nav-item'; li.innerHTML = '<a class="nav-link" href="sign-in.html">Sign In</a>'; nav.appendChild(li);
    }
    if (!document.querySelector('a[href="sign-up.html"]')) {
      var li2 = document.createElement('li'); li2.className = 'nav-item'; li2.innerHTML = '<a class="btn btn-sm btn-primary ms-3" href="sign-up.html">Sign Up</a>'; nav.appendChild(li2);
    }
  }

  // Ensure header nav is minimal: only Category and Sign In/Sign Up
  var collapse = document.getElementById('navbarCollapse');
  if (collapse) {
    collapse.innerHTML = '' +
      '<ul class="navbar-nav navbar-nav-scroll me-auto">' +
      '<li class="nav-item dropdown dropdown-menu-shadow-stacked">' +
      '<a class="nav-link bg-primary bg-opacity-10 rounded-3 text-primary px-3 py-3 py-xl-0" href="#" id="categoryMenu" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
      '<i class="bi bi-ui-radios-grid me-2"></i><span>Category</span></a>' +
      '<ul class="dropdown-menu" aria-labelledby="categoryMenu"><li> <a class="dropdown-item" href="#">View all categories</a></li></ul>' +
      '</li></ul>' +
      '<ul class="navbar-nav ms-auto align-items-center">' +
      '<li class="nav-item"> <a class="nav-link" href="sign-in.html">Sign In</a></li>' +
      '<li class="nav-item"> <a class="btn btn-sm btn-primary ms-3" href="sign-up.html">Sign Up</a></li>' +
      '</ul>';

    // Remove stray theme toggles and other header controls that may have been left behind
    document.querySelectorAll('.mode-switch, button[data-bs-theme-value], .bi-three-dots, .bi-three-dots-vertical').forEach(function(el){ el.remove(); });

    // Remove Accounts nav item (profile/account menu) if present
    var acc = document.getElementById('accounntMenu');
    if (acc) { var accParent = acc.closest('.nav-item'); if (accParent) accParent.remove(); }

    // Remove any leftover "Megamenu" anchors or duplicate Sign In/Sign Up elements outside the main collapse
    document.querySelectorAll('a').forEach(function(a){
      if (a.textContent && a.textContent.trim() === 'Megamenu') {
        var li = a.closest('li'); if (li) li.remove(); else a.remove();
      }
    });

    document.querySelectorAll('a[href="sign-in.html"], a[href="sign-up.html"]').forEach(function(a){
      if (!a.closest('#navbarCollapse')) {
        var li = a.closest('li'); if (li) li.remove(); else a.remove();
      }
    });

    // Remove ellipsis / three-dot menus (static or inside anchors)
    var adv = document.getElementById('advanceMenu');
    if (adv) { var advLi = adv.closest('li'); if (advLi) advLi.remove(); else adv.remove(); }
    document.querySelectorAll('.fa-ellipsis-h, .fa-ellipsis-v, .fas.fa-ellipsis-h').forEach(function(icon){
      var li = icon.closest('li'); if (li) li.remove(); else icon.remove();
    });

    // Remove stray 'Light' text nodes inside header
    var hdr = document.querySelector('header');
    if (hdr) {
      var walker = document.createTreeWalker(hdr, NodeFilter.SHOW_TEXT, null, false);
      var toRemove = [];
      while(walker.nextNode()){
        if (walker.currentNode.nodeValue && walker.currentNode.nodeValue.trim() === 'Light') toRemove.push(walker.currentNode);
      }
      toRemove.forEach(function(n){ if (n.parentNode) n.parentNode.removeChild(n); });
    }
  }

});