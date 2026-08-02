export interface BaseStyles {
    margin: {
      top: number;
      bottom: number;
      left: number;
      right: number;
    };
    padding: {
      top: number;
      bottom: number;
      left: number;
      right: number;
    };
    font: {
      color: string;
      size: number;
      weight: number;
    };
    gbColor: {
      from: string;
      to: string;
    };
    borderRadius: {
      isAll: boolean;
      all: number;
      topLeft: number;
      topRight: number;
      bottomLeft: number;
      bottomRight: number;
    };
  }
  
  /**
   * 基础组件配置接口 - 所有DIY组件共享的基础属性
   */
  export interface BaseDiyConfig {
    diyName: string;
    title: string;
    icon: string;
  }