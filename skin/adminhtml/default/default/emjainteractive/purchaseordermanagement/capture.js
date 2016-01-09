var CapturePayment = Class.create();

CapturePayment.prototype = {
    initialize: function(methodsContainerId, formId){
        this.formId  = formId;
        this.methodsGlobalContainerId = methodsContainerId;
        this.methodsContainerId = methodsContainerId + "_container";
        this.currentMethod = false;
        this.submitForm = false;
    },

    init: function() {
        this.moveMethodsContainerToGlobalContainer(this.methodsGlobalContainerId);
        this.addCaptureTypeListener();
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
        this.submitForm = new varienForm(this.formId);
    },


    changeCaptureType: function(event, directElementRef) {
        element = (event) ? Event.element(event) : directElementRef;
        if ($F(element) == 'online') {
            $(this.methodsContainerId).show();

            currentMethod = false;

            $(this.methodsContainerId).select('input, select, textarea').each(function(field){
                if (($(field).name == 'payment[method]') && ($(field).checked)) {
                    currentMethod = $(field).value;
                }
            });

            if (currentMethod) {
                this.switchMethod(currentMethod)
            }

        } else {
            $(this.methodsContainerId).hide();
            $(this.methodsContainerId).select('input, select, textarea').each(function(field){
                if ($(field).name != 'payment[method]') {
                    $(field).disabled = true;
                }
            });
        }
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
    },

    addCaptureTypeListener: function() {
        var elements = Form.getElements(this.formId);

        for (var i=0; i<elements.length; i++) {
            if (elements[i].name == 'invoice[capture_case]') {
                $(elements[i]).observe('change', this.changeCaptureType.bind(this));
                this.changeCaptureType(null, elements[i]);
            }
        }
    },


    moveMethodsContainerToGlobalContainer: function(methodsContainerId) {
        if ($(methodsContainerId) && $(this.formId)) {
            paymentMethodBlockHeader = $(this.formId).getElementsByClassName('head-payment-method');
            paymentMethodBlockHeader = paymentMethodBlockHeader[0];
            paymentMethodBlock = $(paymentMethodBlockHeader).up(2);
            nextClearBlock = $(paymentMethodBlock).next('div.clear');

            new Insertion.After(
                $(nextClearBlock),
                $(methodsContainerId).innerHTML
            );

            Element.remove($(methodsContainerId));
        }
    }
};
