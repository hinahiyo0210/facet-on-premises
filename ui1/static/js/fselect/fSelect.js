/* dev founer feihan */
(function($) {

    $.fn.fSelect = function(options) {

        if (typeof options == 'string' ) {
            var settings = options;
        }
        else {
            var settings = $.extend({
                placeholder: '',
                numDisplayed: 3,
                overflowText: '{n} selected',
                searchText: '検索',
                showSearch: true
            }, options);
        }


        /**
         * Constructor
         */
        function fSelect(select, settings) {
            this.$select = $(select);
            this.settings = settings;
            this.create();
        }


        /**
         * Prototype class
         */
        fSelect.prototype = {
            create: function() {
                var multiple = this.$select.is('[multiple]') ? ' multiple' : '';
                this.$select.wrap('<div class="fs-wrap' + multiple + '"></div>');
                this.$select.before('<div class="fs-label-wrap"><div class="fs-label">' + this.settings.placeholder + '</div></div>');
                this.$select.before('<div class="fs-dropdown hidden"><div class="fs-options"></div></div>');
                this.$select.addClass('hidden');
                this.$wrap = this.$select.closest('.fs-wrap');
                this.reload();
            },

            reload: function() {
                if (this.settings.showSearch) {
                    var selectAll = this.$select.is('[multiple]') ? '<div class="fs-selectAll"><i></i></div>' : '';
                    var search = '<div class="fs-search"' + (this.$select.is('[no-search]') ? ' style="display: none;"' : '')
                        + '><input type="search" placeholder="' + this.settings.searchText + '"/>' + selectAll + '</div>';
                    this.$wrap.find('.fs-dropdown').prepend(search);
                }
                var choices = this.buildOptions(this.$select);
                this.$wrap.find('.fs-options').html(choices);
                this.$wrap.data('oldVal',this.$wrap.fSelectedValues())
                this.$wrap.find('.fs-selectAll').change();
                this.reloadDropdownLabel();
            },

            destroy: function() {
                this.$wrap.find('.fs-label-wrap').remove();
                this.$wrap.find('.fs-dropdown').remove();
                this.$select.unwrap().removeClass('hidden');
            },

            buildOptions: function ($element) {
              var $this = this;

              var choices = '';
              var dataIndex = 0;
              $element.children().each(function (i, el) {
                var $el = $(el);

                if ('optgroup' == $el.prop('nodeName').toLowerCase()) {
                  choices += '<div class="fs-optgroup">';
                  choices += '<div class="fs-optgroup-label">' + $el.prop('label') + '</div>';
                  choices += $this.buildOptions($el);
                  choices += '</div>';
                }
                else {
                  var selected = $el.is('[selected]') ? ' selected' : '';
                  choices += '<div class="fs-option' + selected + '" data-value="' + $el.prop('value') +'" data-index="' + dataIndex +'"><span class="fs-checkbox"><i></i></span><div class="fs-option-label">' + $el.html() + '</div></div>';
                  dataIndex++
                }
              });

              return choices;
            },

            reloadDropdownLabel: function() {
                var settings = this.settings;
                var labelText = [];

                this.$wrap.find('.fs-option.selected').each(function(i, el) {
                    labelText.push($(el).find('.fs-option-label').text());
                });

                if (labelText.length < 1) {
                    labelText = settings.placeholder;
                }
                // else if (labelText.length > settings.numDisplayed) {
                //     labelText = settings.overflowText.replace('{n}', labelText.length);
                // }
                else {
                    labelText = labelText.join(', ');
                }

                this.$wrap.find('.fs-label').html(labelText);
                this.$select.change();

            }
        }


        /**
         * Loop through each matching element
         */
        return this.each(function() {
            var data = $(this).data('fSelect');

            if (!data) {
                data = new fSelect(this, settings);
                $(this).data('fSelect', data);
            }

            if (typeof settings == 'string') {
                data[settings]();
            }
        });
    }


    /**
     * Events
     */
    window.fSelect = {
        'active': null,
        'idx': -1
    };

    function setIndexes($wrap) {
      $wrap.find('.fs-option').removeClass('hl');
      $wrap.find('.fs-search input').focus();
      window.fSelect.idx = -1;
    }

    function setScroll($wrap) {
        var $container = $wrap.find('.fs-options');
        var $selected = $wrap.find('.fs-option.hl');

        var itemMin = $selected.offset().top + $container.scrollTop();
        var itemMax = itemMin + $selected.outerHeight();
        var containerMin = $container.offset().top + $container.scrollTop();
        var containerMax = containerMin + $container.outerHeight();

        if (itemMax > containerMax) { // scroll down
            var to = $container.scrollTop() + $selected.outerHeight();
            $container.scrollTop(to);
        }
        else if (itemMin < containerMin) { // scroll up
            var to = $container.scrollTop() - $selected.outerHeight();
            $container.scrollTop(to);
        }
    }

    function closePulldown() {
        $wrap = window.fSelect.active
        if ($wrap){
            $wrap.find('.fs-dropdown').addClass('hidden')
            let changed
            if ($wrap.hasClass('multiple')) {
                const oldVal = $wrap.data('oldVal')||[]
                const newVal = $wrap.fSelectedValues()
                changed = _.xor(oldVal,newVal).length>0
            } else {
                changed =  $wrap.data('oldVal')!=$wrap.fSelectedValues()
            }
            if (changed){
                $wrap.find('select').trigger('pulldownChange')
            }
        }
    }
    $(document).on('change','.fs-selectAll',function(){
        $(this).addClass('selected');
        const visibleOpts = $(this).parent().next().find('.fs-option').not(".hidden")
        if (visibleOpts.length === 0 || visibleOpts.not(".selected").length > 0) {
            $(this).removeClass('selected');
        }
    });


    $(document).on('click', '.fs-selectAll', function () {
      var $wrap = $(this).closest('.fs-wrap');
      var selected = []
      if($(this).hasClass('selected')) {
        $wrap.find('.fs-option').not(".hidden").removeClass('selected')
      }else {
        var changeVal = []
        var oldVal = $wrap.find('select').val()
        var $changeOptionList = $wrap.find('.fs-option').not(".hidden").not(".selected")
        $changeOptionList.each(function (i, el) {
          changeVal.push($(el).attr('data-value'));
        });
        $changeOptionList.addClass('selected')
        selected = oldVal.concat(changeVal)
      }
      $wrap.find('select').val(selected);
      $wrap.find('select').fSelect('reloadDropdownLabel');
      $(this).toggleClass('selected')
    });

    $(document).on('click', '.fs-option', function() {
        var $wrap = $(this).closest('.fs-wrap');

        if ($wrap.hasClass('multiple')) {
            var selected = [];

            $(this).toggleClass('selected');
            $wrap.find('.fs-option.selected').each(function(i, el) {
                selected.push($(el).attr('data-value'));
            });

            $wrap.find('select').val(selected);
            $wrap.find('select').fSelect('reloadDropdownLabel');
            $('.fs-selectAll').change();
        }
        else {
            var selected = $(this).attr('data-value');
            $wrap.find('.fs-option').removeClass('selected');
            $(this).addClass('selected');
            $wrap.find('select').val(selected);
            $wrap.find('select').fSelect('reloadDropdownLabel');
            $('.fs-selectAll').change();
            closePulldown()
            window.fSelect.active = null
        }


    });
    $(document).on('keyup', '.fs-search input', function(e) {
        if (40 == e.which) {
            $(this).blur();
            return;
        }
    });
    $(document).on('input', '.fs-search input', function() {

        var $wrap = $(this).closest('.fs-wrap');
        var keywords = $(this).val();

        $wrap.find('.fs-option, .fs-optgroup-label').removeClass('hidden');

        if ('' != keywords) {
            var regex = new RegExp(keywords, 'gi');
            $wrap.find('.fs-option').each(function() {
                if (null === $(this).find('.fs-option-label').text().match(regex)) {
                    $(this).addClass('hidden');
                }
            });

            $wrap.find('.fs-optgroup-label').each(function() {
                var num_visible = $(this).closest('.fs-optgroup').find('.fs-option:not(.hidden)').length;
                if (num_visible < 1) {
                    $(this).addClass('hidden');
                }
            });
        }

        setIndexes($wrap);
        $('.fs-selectAll').change();
    });

    $(document).on('click', function(e) {
        var $el = $(e.target);
        var $wrap = $el.closest('.fs-wrap');

        if (0 < $wrap.length) {
            if ($el.hasClass('fs-label')||$el.hasClass('fs-arrow')) {
                window.fSelect.active = $wrap;
                var is_hidden = $wrap.find('.fs-dropdown').hasClass('hidden');
                $('.fs-dropdown').addClass('hidden');

                if (is_hidden) {
                    $wrap.find('.fs-dropdown').removeClass('hidden');
                }
                else {
                    closePulldown()
                }

                setIndexes($wrap);
            }
        }
        else {
            closePulldown()
            window.fSelect.active = null
        }
    });

    $(document).on('keydown', function (e) {
      var $wrap = window.fSelect.active;
      if (null === $wrap) {
        return;
      }
      window.fSelect.idx = window.fSelect.idx == undefined ? -1 : window.fSelect.idx
      var $idx = $wrap.find('.fs-option[data-index='+ window.fSelect.idx +']')
      var $idxPrev = $idx.prevAll().not(".hidden").first()
      var $idxNext = $idx.nextAll().not(".hidden").first()


      if (38 == e.which) { // up
        e.preventDefault();
        $wrap.find('.fs-option').removeClass('hl');
        if ($idx.length > 0 && $idxPrev.length > 0) {
          window.fSelect.idx = $idxPrev.attr('data-index')
          $wrap.find('.fs-option[data-index=' + window.fSelect.idx + ']').addClass('hl');
          setScroll($wrap);
        }else {
          window.fSelect.idx = -1;
          $wrap.find('.fs-search input').focus();
        }
      }
      else if (40 == e.which) { // down
        e.preventDefault();
        var $showOptionList = $wrap.find('.fs-option').not(".hidden")
        var last_index = $showOptionList.eq($showOptionList.length - 1).attr('data-index');
        if (parseInt(window.fSelect.idx) < parseInt(last_index)) {
          var idxIndex = -1
          $showOptionList.each(function(index, item){
            if ($showOptionList.eq(index).attr('data-index') == window.fSelect.idx && idxIndex == -1) {
              idxIndex = index
            }
          })
          window.fSelect.idx = window.fSelect.idx == -1 ? $showOptionList.eq(0).attr('data-index') : $showOptionList.eq(idxIndex+1).attr('data-index')
          $wrap.find('.fs-option').removeClass('hl');
          $wrap.find('.fs-option[data-index=' + window.fSelect.idx + ']').addClass('hl');
          setScroll($wrap);
        }
      }
      else if (32 == e.which || 13 == e.which) { // space, enter
          const selected = $wrap.find('.fs-option.hl')
          if (selected.length && 32 == e.which) {
              //prevent scroll down
              e.preventDefault()
          }
          selected.click()
      }
      else if (27 == e.which) { // esc
        closePulldown()
        window.fSelect.active = null
      }
    });

    $.fn.fSelectedValues=function(val){
        const $wrap = $(this).closest('.fs-wrap')
        if (val !== undefined) {
            if ($wrap.hasClass('multiple')) {
                var wrapVal = $wrap.find('select').val()
                $wrap.find('.fs-option').each(function () {
                    const $option = $(this)
                    const selected = $option.hasClass('selected')
                    const value = $option.attr('data-value')
                    const index = val.indexOf(value)
                    if (index == -1 && selected) {
                        // 不需要选中但是已经选中的
                        $option.removeClass('selected')
                        wrapVal.splice(index,1)
                    }else if(index != -1 && !selected){
                        // 需要选中但是未选中的
                        $option.addClass('selected')
                        wrapVal.push(value);
                    }
                })
                if ($wrap.find('.fs-option').not(".hidden").length == wrapVal.length) {
                    $wrap.find('.fs-selectAll').addClass('selected')
                }else {
                    $wrap.find('.fs-selectAll').removeClass('selected')
                }
                $wrap.find('select').val(wrapVal)
                $wrap.find('select').fSelect('reloadDropdownLabel')
            } else {
                $wrap.find('.fs-option').each(function () {
                    const $option = $(this)
                    const selected = $option.hasClass('selected')
                    const value = $option.attr('data-value')
                    if (val === value ) {
                        $option.click()
                        return false
                    }
                    return true
                })
            }
        } else {
            let result
            if ($wrap.hasClass('multiple')) {
                result=[]
                const $selects = $wrap.find("option:selected")
                for(let i=0;i<$selects.length;i++){
                    result.push($selects[i].value)
                }
            } else {
                result = $wrap.find("option:selected")[0]?.value
            }
            return result
        }
    }

    $.fn.fSelectedTexts=function(splitString){
        var result="";
        var $selects=this.find("option:selected");
        for(var i=0;i<$selects.length;i++){
            result+=$selects[i].text+((i==$selects.length-1)?"":splitString);
        }
        return result;
    }

})(jQuery);