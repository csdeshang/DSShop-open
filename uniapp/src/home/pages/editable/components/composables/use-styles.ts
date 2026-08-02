import { computed, type ComputedRef } from 'vue';
import type { BaseStyles } from './common-types';

/**
 * 计算容器样式
 * @param baseStyles 基础样式对象或计算属性
 * @returns 计算后的容器样式对象
 */
export function useContainerStyle(baseStyles: ComputedRef<BaseStyles | undefined>) {
    return computed(() => {
      if (!baseStyles.value) return {};
  
      const { margin, padding, gbColor, borderRadius } = baseStyles.value;
  
      let borderRadiusStyle = '';
      if (borderRadius) {
        if (borderRadius.isAll) {
          borderRadiusStyle = `${borderRadius.all}px`;
        } else {
          borderRadiusStyle = `${borderRadius.topLeft || 0}px ${borderRadius.topRight || 0}px ${borderRadius.bottomRight || 0}px ${borderRadius.bottomLeft || 0}px`;
        }
      }
  
      return {
        marginTop: `${margin?.top || 0}px`,
        marginBottom: `${margin?.bottom || 0}px`,
        marginLeft: `${margin?.left || 0}px`,
        marginRight: `${margin?.right || 0}px`,
        paddingTop: `${padding?.top || 0}px`,
        paddingBottom: `${padding?.bottom || 0}px`,
        paddingLeft: `${padding?.left || 0}px`,
        paddingRight: `${padding?.right || 0}px`,
        background: gbColor?.from === gbColor?.to
          ? gbColor?.from
          : `linear-gradient(to bottom, ${gbColor?.from || '#fff'}, ${gbColor?.to || '#fff'})`,
        borderRadius: borderRadiusStyle,
      };
    });
  }
  
  /**
   * 计算字体样式
   * @param baseStyles 基础样式对象或计算属性
   * @returns 计算后的字体样式对象
   */
  export function useFontStyle(baseStyles: ComputedRef<BaseStyles | undefined>) {
    return computed(() => {
      const font = baseStyles.value?.font;
      if (!font) return {};
  
      return {
        color: font.color || '#333333',
        fontSize: `${font.size || 13}px`,
        fontWeight: font.weight || 400
      };
    });
  }