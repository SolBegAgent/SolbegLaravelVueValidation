(function (global) {
    global.solbeg = global.solbeg || {};

    var isArray = global.Array.isArray;

    var MessagesBag = function (errorMessages) {
        this.init(errorMessages);
    };
    MessagesBag.prototype = {
        messages: {},

        init: function (errorMessages) {
            var groupedMessages = {},
                field, rule, params, message;

            errorMessages = errorMessages || [];
            for (var i = 0, cnt = errorMessages.length; i < cnt; ++i) {
                field = errorMessages[i][0];
                rule = errorMessages[i][1];
                params = errorMessages[i][2];
                message = errorMessages[i][3];

                if (!groupedMessages[field]) {
                    groupedMessages[field] = {};
                }
                if (!groupedMessages[field][rule]) {
                    groupedMessages[field][rule] = [];
                }
                groupedMessages[field][rule].push([params, message]);
            }

            this.messages = groupedMessages;
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

    global.solbeg.vueErrorMessagesBag = MessagesBag;

    global.solbeg.initVueValidation = function (vueOptions, errorMessages) {
        var vueObj = new Vue(vueOptions || {}),
            defaultMessageFormatter = vueObj.$validator._formatErrorMessage,
            msgBag = new global.solbeg.vueErrorMessagesBag(isArray(errorMessages) ? errorMessages : []);

        vueObj.$validator._formatErrorMessage = function (field, rule) {
            var msg = this._getErrorMessagesBag().get(field, rule.name, rule.params);
            return msg || defaultMessageFormatter.apply(this, arguments);
        };
        vueObj.$validator._getErrorMessagesBag = function () {
            return msgBag;
        };

        return vueObj;
    };
})(this);
