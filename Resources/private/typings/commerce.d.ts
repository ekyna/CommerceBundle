interface AddToCartEvent {
    type: string
    data: any
    jqXHR: JQueryXHR
    success: boolean,
    modal: Ekyna.Modal
}

declare class Widget {
    reload(): void
}

declare let init:() => void;

declare module "ekyna-commerce/commerce" {
    export = {
        init: init,
        Widget: Widget
    };
}
