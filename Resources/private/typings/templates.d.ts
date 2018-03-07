interface Template {
    render(data: any): string
}

declare let Templates:{[id:string]: Template};

declare module "ekyna-commerce/templates" {
    export = Templates;
}
