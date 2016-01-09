var CapturePayment = Class.create();

CapturePayment.prototype = {
    initialize: function(methodsContainerId, formId){
        this.formId  = formId;
        this.methodsContainerId = methodsContainerId + "_container";
        this.currentMethod = false;
        this.submitForm = false;
    },
    init: function() {
        this.initPaymentForm();
        this.addSubmitFormHandler();
    },
    submitDataForm: function(event) {
        $(this.submitForm).submit();
    },
    addSubmitFormHandler: function() {
        button = $(this.formId).select('button.submit-button');
        button = button[0];
        $(button).writeAttribute('onclick', false);
        $(button).observe('click', this.submitDataForm.bind(this));
        this.submitForm = new VarienForm(this.formId);
    },

    changeVisible: function(method, mode) {
        var block = 'payment_form_' + method;
        [block + '_before', block, block + '_after'].each(function(el) {
            element = $(el);
            if (element) {
                element.style.display = (mode) ? 'none' : '';
                element.select('input', 'select', 'textarea').each(function(field) {
                    field.disabled = mode;
                });
            }
        });
    },

    switchMethod: function(method){
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
            this.changeVisible(this.currentMethod, true);
        }
        if ($('payment_form_'+method)){
            this.changeVisible(method, false);
            $('payment_form_'+method).fire('payment-method:switched', {method_code : method});
        } else {
            //Event fix for payment methods without form like "Check / Money order"
            document.body.fire('payment-method:switched', {method_code : method});
        }
        this.currentMethod = method;
    },

    initPaymentForm: function() {
        var method = false;
        $(this.methodsContainerId).select('input, select, textarea').each(function(el){
            if ($(el).name == 'payment[method]') {
                if ($(el).checked) {
                    method = $(el).value;
                }
            } else {
                $(el).disabled = true;
            }
            $(el).setAttribute('autocomplete','off');
        }.bind(this));
        if (method) this.switchMethod(method);
    }
};

