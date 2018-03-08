interface Template {
    render(data: any): string
}

let init:() => void;

export class Widget {
    reload(): void
}

declare module "ekyna-commerce/commerce" {
    export = {
        init: init,
        Widget: Widget
    };
}
