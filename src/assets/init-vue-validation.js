(function (global) {
    var isArray = global.Array.isArray;

    var MessagesBag = function (errorMessages) {
        this.init(errorMessages);
    };
    MessagesBag.prototype = {
        messages: {},

        init: function (errorMessages) {
            var field, i, cnt;

            this.messages = {};
            errorMessages = errorMessages || [];

            for (i = 0, cnt = errorMessages.length; i < cnt; ++i) {
                field = errorMessages[i][0];
                this.add(field, errorMessages[i].slice(1));
            }
        },

        add: function (field, messages) {
            var rule, params, message;

            messages = messages || [];
            for (var i = 0, cnt = messages.length; i < cnt; ++i) {
                rule = messages[i][0];
                params = messages[i][1];
                message = messages[i][2];

                if (!this.messages[field]) {
                    this.messages[field] = {};
                }
                if (!this.messages[field][rule]) {
                    this.messages[field][rule] = [];
                }
                this.messages[field][rule].push([params, message]);
            }
        },

        has: function (field, rule, params) {
            return this.get(field, rule, params) !== null;
        },

        get: function (field, rule, params) {
            if (!this.messages[field] || !this.messages[field][rule]) {
                return null;
            }

            for (var i = 0, cnt = this.messages[field][rule].length; i < cnt; ++i) {
                if (this.paramsAreEqual(this.messages[field][rule][i][0], params)) {
                    return this.messages[field][rule][i][1];
                }
            }

            return null;
        },

        paramsAreEqual: function (params1, params2) {
            if (!isArray(params1) || !isArray(params2)) {
                return false;
            } else if (params1.length !== params2.length) {
                return false;
            }

            for (var i = 0, cnt = params1.length; i < cnt; ++i) {
                if (('' + params1[i]) !== ('' + params2[i])) {
                    return false;
                }
            }
            return true;
        }
    };

    global.SolbegLaravelVueValidation = {
        MessagesBag: MessagesBag,
        currentValidator: null,

        install: function (Vue, options) {
            Vue.use(global.VeeValidate);
            global.VeeValidate.Validator.extend('same', this.sameValidator.bind(this));

            Vue.directive('form-validation', this.formValidationDirective());
            Vue.directive('validation-messages', this.validationMessagesDirective());
            Vue.directive('validation-error', this.validationErrorDirective());
        },

        extendValidator: function ($validator, errorMessages) {
            var thisPlugin = this,
                parentMessageFormatter = $validator._formatErrorMessage,
                parentTestMethod = $validator._test,
                msgBag = new thisPlugin.MessagesBag(isArray(errorMessages) ? errorMessages : []);

            $validator._getErrorMessagesBag = function () {
                return msgBag;
            };
            $validator._formatErrorMessage = function (field, rule) {
                var msg = this._getErrorMessagesBag().get(field, rule.name, rule.params);
                return msg || parentMessageFormatter.apply(this, arguments);
            };
            $validator._test = function () {
                var result;

                thisPlugin.currentValidator = this;
                try {
                    result = parentTestMethod.apply(this, arguments);
                } catch (err) {
                    thisPlugin.currentValidator = null;
                    throw err;
                }
                thisPlugin.currentValidator = null;

                return result;
            };
        },

        sameValidator: function (value, args) {
            var field = args && args[0];
            if (!field) {
                throw new Error('Same validator requires the first parameter as name of field.');
            }

            var validator = this.currentValidator,
                selector = 'input[name="' + field + '"]',
                input = validator && validator.$vm && validator.$vm.$el && validator.$vm.$el.querySelector(selector);

            return !! (input && global.String(value) === input.value);
        },

        fillErrors: function (vueObj, errors, scope) {
            for (var field in errors) {
                if (errors.hasOwnProperty(field)) {
                    vueObj.$validator.errorBag.add(field, errors[field], scope);
                }
            }
        },

        parseExpression: function (expr) {
            if (!expr) {
                return null;
            } else if ((typeof expr !== 'string') && !(expr instanceof global.String)) {
                return expr;
            }

            try {
                return global.JSON.parse(expr);
            } catch (err) {
                global.console && global.console.error && global.console.error(err);
                return null;
            }
        },

        formValidationDirective: function () {
            var thisPlugin = this;
            return {
                bind: function () {
                    var vueObj = this.vm,
                        form = this.el,
                        props = thisPlugin.parseExpression(this.expression) || {};

                    thisPlugin.extendValidator(this.vm.$validator, props.messages || []);
                    vueObj.$nextTick(function () {
                        var scope = form.dataset.scope || undefined;
                        thisPlugin.fillErrors(vueObj, props.errors || {}, scope);
                    });
                }
            };
        },

        validationMessagesDirective: function () {
            var thisPlugin = this;
            return {
                bind: function () {
                    var field = (this.el.dataset && this.el.dataset.as) || this.el.name;
                    var messages = thisPlugin.parseExpression(this.expression) || {};
                    this.vm.$validator._getErrorMessagesBag().add(field, messages || []);
                }
            };
        },

        validationErrorDirective: function () {
            var thisPlugin = this;
            return {
                bind: function () {
                    var element = this.el;
                    var error = this.expression || null;

                    if (!error) {
                        return;
                    }

                    this.vm.$nextTick(function () {
                        this.$nextTick(function () { // double $nextTick to prevent clearing errors at once after binding
                            var scope = element.dataset.scope || (element.form && element.form.dataset.scope) || undefined,
                                errors = {};
                            errors[element.name] = error;
                            thisPlugin.fillErrors(this, errors, scope);
                        });
                    });
                }
            };
        }
    };
})(this);