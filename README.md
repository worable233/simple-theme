# Simple Theme

## 项目简介

Simple Theme 是一个基于 Vue 3、Vite 与 WordPress REST API 的前台主题。它把 WordPress 作为内容后台，前端通过主题内置的 Vue 应用渲染首页、文章、页面与归档内容。

## 项目特点

- 使用 Vue 3 + TypeScript + Vite 作为前端开发与构建方案。
- 通过 WordPress REST API 拉取站点信息、菜单、文章与归档数据。
- 内置路径解析接口，支持文章、页面、分类/标签归档和 404 页面渲染。
- 构建产物通过 Vite `manifest.json` 注入到主题中，资源路径更清晰。

## 项目代办

- 补充搜索、分页、评论等常见前台能力。
- 完善 SEO 元信息、分享信息与结构化数据。
- 增加自动化测试、发布流程与部署说明。
- 丰富主题样式和可配置项。

## 项目构建

安装前端依赖：

```sh
npm install
```

本地开发：

```sh
npm run dev
```

生产构建：

```sh
npm run build
```

代码检查：

```sh
npm run lint
```

如需安装 PHP 侧开发工具：

```sh
composer install
```

## 协议

本项目采用 [CC BY-NC-ND 4.0](https://creativecommons.org/licenses/by-nc-nd/4.0/) 协议发布，详细说明见 [LICENSE](./LICENSE)。
