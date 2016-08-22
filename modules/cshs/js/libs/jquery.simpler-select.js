/**
 * jQuery Plugin which renders a standard select with hierarchical options as
 * a set of selects, one for each level of the hierarchy.
 */
(function ($, pluginName) {
  'use strict';

  // Create the defaults once.
  var defaults = {
    noneLabel: '- Please choose -',
    noneValue: '_none',
    labels: []
  };

  // The actual plugin constructor.
  function Plugin(element, options) {
    this.$element = $(element);
    this.$currentSelect = null;

    // vars
    this.settings = $.extend({}, defaults, options);
    this.selectOptions = [];

    this.init();
  }

  Plugin.prototype = {
    init: function () {
      var that = this;
      // Ensure that we'll clearly initiate a new instance.
      that.destroy();
      that.$element.find('option').each(function () {
        var $option = $(this);

        that.selectOptions.push({
          value: $option.val(),
          label: $option.text(),
          parent: $option.data('parent') || 0,
          children: []
        });
      });

      var tree = that.buildTree(that.selectOptions);

      if (tree === null) {
        return;
      }

      var initialValue = that.$element.val();
      var initialParents = [];
      var $selectElement = that.createSelect(tree);
      var $currentSelect = $selectElement;

      if (initialValue) {
        // console.log(typeof initialValue);
        if (typeof initialValue !== 'string') {
          // if array, flatten it
          initialValue = initialValue.shift();
        }

        // get all parents, starting from the initial value
        initialParents = that.getAllParents(initialValue);
        // reverse the parents, that they start from the root
        initialParents.reverse();
        // add the current value as the last leave.
        initialParents.push(initialValue);
      }

      this.$element.after($selectElement);

      $.each(initialParents, function (idx, value) {
        that.selectSetValue($currentSelect, value);

        var optionInfo = that.getOptionInfoByValue(value);
        var $nextSelect = that.createSelect(optionInfo.children, value, idx + 1);

        if ($nextSelect !== null) {
          $currentSelect.after($nextSelect);
          $currentSelect = $nextSelect;
        }
      });

      // Hide the original.
      that.$element.hide();
    },

    /**
     * Destroy CSHS.
     */
    destroy: function () {
      this.selectOptions = [];
      this.$element.show().nextAll('.select-wrapper').remove();
    },

    /**
     * Given an array of options, build an HTML select element
     *
     * @param {HTMLElement[]|HTMLOptionElement[]} options
     *   List of options.
     * @param {String} [parent]
     *   Parent option.
     * @param {Number} [level]
     *   Nesting level.
     *
     * @return {jQuery|null}
     *   Newly created element.
     */
    createSelect: function (options, parent, level) {
      if (!options || options.length < 1) {
        return null;
      }

      parent = parent || this.settings.noneValue;
      level = level || 0;

      var that = this;
      var $select = $('<select class="form-select simpler-select">');

      if (that.$element.hasClass('error')) {
        $select.addClass('error');
      }

      // Add the _none option always.
      $select.append('<option value="' + that.settings.noneValue + '" data-parent-value="' + parent + '">' + that.settings.noneLabel + '</option>');

      $.each(options, function (idx, option) {
        if (option.value == that.settings.noneValue) {
          // Do not add _none options (Already added by code above).
          return true;
        }

        var $option = $('<option>')
          .val(option.value)
          // Remove dashes from the beginning, then set the label.
          .text(option.label.replace(/(- )+/, ''));

        if (option.children.length) {
          $option.addClass('has-children');
        }

        $select.append($option);
      });

      $select.change(function () {
        that.$currentSelect = $(this);
        // Remove deeper selects.
        that.selectRemoveNext(that.$currentSelect);

        // Get the selected value and also set the original drop-down.
        var $selected = that.$currentSelect.find('option:selected');
        var selectedValue = $selected.val();
        var parentValue = $selected.data('parent-value');

        if (typeof parentValue === 'undefined') {
          parentValue = selectedValue;
        }

        that.$element
          .val(parentValue)
          .change();

        if (selectedValue == that.settings.noneValue) {
          return;
        }

        // Build new child select.
        var optionInfo = that.getOptionInfoByValue(selectedValue);

        if (typeof optionInfo.children !== 'undefined') {
          that.addSelectAfter(that.createSelect(optionInfo.children, selectedValue, that.selectGetLevel()));
        }
      });

      var $wrapper = $('<div class="select-wrapper"><label>' + (this.settings.labels[level] || '') + '</label></div>');
      $wrapper.find('label').after($select);

      return $wrapper;
    },

    /**
     * Given an flat array an tree is built.
     *
     * @param {Object[]} array
     *   Options list.
     * @param {Object} [parent]
     *   Parent option.
     * @param {Array} [tree]
     *   Existing options.
     *
     * @return {Array}
     *   Options tree.
     */
    buildTree: function (array, parent, tree) {
      var children = [];

      tree = tree || [];
      parent = parent || {value: 0};

      $.each(array, function (value, child) {
        if (typeof child !== 'undefined') {
          // Here must be no strict comparison!
          if (child.parent == parent.value) {
            children.push(child);
          }
        }
      });

      if (children.length) {
        if (parent.value == 0) {
          tree = children;
        }
        else {
          parent['children'] = children;
        }

        for (var i = 0; i < children.length; i++) {
          this.buildTree(array, children[i], tree);
        }
      }

      return tree;
    },

    /**
     * Set the value of a select to the given.
     *
     * @param {jQuery} $select
     *   Wrapper element.
     * @param {String} value
     *   New value to set.
     */
    selectSetValue: function ($select, value) {
      $select.find('select').val(value);
    },

    /**
     * Remove all following selects.
     */
    selectRemoveNext: function () {
      var $wrapper = this.$currentSelect.parents('.select-wrapper').nextAll('.select-wrapper');

      if (typeof $wrapper !== 'undefined') {
        $wrapper.remove();
      }
    },

    /**
     * Add a newSelect after the currentSelect.
     *
     * @param {jQuery} $newSelect
     *   New "select" element.
     */
    addSelectAfter: function ($newSelect) {
      this.$currentSelect.parents('.select-wrapper').after($newSelect);
    },

    /**
     * Get the hierarchy level of given select.
     *
     * @return {Number}
     *   Number of wrappers.
     */
    selectGetLevel: function () {
      return this.$currentSelect.parents('.form-type-cshs').find('.select-wrapper').length;
    },

    /**
     * Given a value build an array of all parents (from leave to root).
     *
     * @param {String} value
     *   Value of option.
     * @param {Array} [parents]
     *   Parent options.
     *
     * @return {Array}
     *   Updated parent options list.
     */
    getAllParents: function (value, parents) {
      if (value == this.settings.noneValue) {
        return [];
      }

      parents = parents || [];

      var $option = this.getOptionByValue(value);
      var parent = $option.data('parent');

      if (typeof parent !== 'undefined' && parent != 0) {
        parents.push(parent);
        this.getAllParents(this.getOptionByValue(parent).val(), parents);
      }

      return parents;
    },

    /**
     * Tiny helper to get the option jQuery object.
     *
     * @param {String} value
     *   Value of an option.
     *
     * @return {jQuery}
     *   Element.
     */
    getOptionByValue: function (value) {
      return this.$element.find('option[value="' + value + '"]');
    },

    /**
     * Helper to get the info-object which corresponds to an option value.
     *
     * @param {String} value
     *   Value of an option.
     *
     * @return {Object}
     *   Element.
     */
    getOptionInfoByValue: function (value) {
      var optionInfo = {};

      $.each(this.selectOptions, function (idx, option) {
        if (option.value == value) {
          optionInfo = option;
          return false;
        }
      });

      return optionInfo;
    }
  };

  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[pluginName] = function (options) {
    this.each(function () {
      if (!$.data(this, 'plugin_' + pluginName)) {
        $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
      }
    });

    // chain jQuery functions
    return this;
  };

})(jQuery, 'simplerSelect');
