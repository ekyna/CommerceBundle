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

interface Commerce {
    init(): void
}

declare let commerce:Commerce;

declare module "ekyna-commerce/commerce" {
    export = commerce;
}
