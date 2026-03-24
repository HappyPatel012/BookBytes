import { Slots, VNode, VNodeProps } from 'vue-demi';
interface Options extends VNodeProps {
    class?: string;
    domProps?: VNodeProps;
    on?: Record<string, () => void>;
    props?: VNodeProps;
    style?: string;
    'aria-hidden'?: string;
}
/**
 * hDemi function.
 */
export default function h(type: string | Record<any, any>, options?: Options, chidren?: any): VNode;
export declare const slot: (defaultSlots: any) => Slots;
export {};
