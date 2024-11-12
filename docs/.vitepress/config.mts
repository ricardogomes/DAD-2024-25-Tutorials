import { defineConfig } from "vitepress";

// https://vitepress.dev/reference/site-config
export default defineConfig({
  head: [["link", { rel: "icon", href: "/assets/favicon.ico" }]],
  title: "Project Tutorials",
  description: "Tutorials related to the DAD 2023/24 course project",
  cleanUrls: true,
  lastUpdated: true,
  themeConfig: {
    search: {
      provider: "local",
    },
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: "Home", link: "/" },
      {
        text: "Tutorials",
        items: [
          { text: "Deploy", link: "/deploy" },
          {
            text: "Tools",
            items: [
              {
                text: "Windows Subsystem for Linux",
                link: "./wsl.md",
              },
              { text: "Docker", link: "/docker" },
              { text: "Laravel Sail", link: "/sail" },
            ],
          },
          // {
          //   text: "Project",
          //   items: [
          //     {
          //       text: "Connect to the VM",
          //       link: "./connect.md",
          //     },
          //     { text: "Install Components", link: "/install" },
          //     { text: "Deploy Code", link: "/deploy" },
          //     {
          //       text: "Intermediate Submission",
          //       link: "./intermediate.md",
          //     },
          //   ],
          // },
          {
            text: "Auxiliary",
            items: [
              {
                text: "IPLeiria - VPN",
                link: "./vpn.md",
              },
              // {
              //   text: "Laravel Authentication - Passport",
              //   link: "./passport.md",
              // },
              // {
              //   text: "Web Sockets - SocketIO",
              //   link: "./socketio.md",
              // },
              // {
              //   text: "Windows Subsystem for Linux",
              //   link: "./wsl.md",
              // },
            ],
          },
        ],
      },
      {
        text: "Resources",
        items: [
          {
            text: "Infrastructure",
            items: [
              {
                text: "Kubectl Documentation",
                link: "https://kubernetes.io/docs/tasks/tools/install-kubectl-macos/",
              },
              {
                text: "Docker Documentation",
                link: "https://docs.docker.com/reference/cli/docker/",
              },
              {
                text: "Justfile",
                link: "https://github.com/casey/just",
              },
            ],
          },
          {
            text: "Tech Stack",
            items: [
              {
                text: "Laravel Documentation",
                link: "https://laravel.com/docs/11.x",
              },
              {
                text: "VueJS Documentation",
                link: "https://vuejs.org/guide/introduction.html",
              },
              {
                text: "SocketIO Documentation",
                link: "https://socket.io/docs/v4/",
              },
            ],
          },
        ],
      },
    ],
    footer: {
      message: "IPLeiria | ESTG | EI | DAD 2023/24",
    },
    socialLinks: [
      {
        icon: "github",
        link: "https://github.com/ricardogomes/DAD-2023-24-Project-Tutorials",
      },
    ],
  },
});
