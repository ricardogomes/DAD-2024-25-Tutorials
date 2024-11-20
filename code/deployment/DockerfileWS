FROM oven/bun:1
WORKDIR /app

COPY package.json ./
RUN bun install

COPY . .

EXPOSE 8080

ENV APP_PORT=8080
CMD ["bun", "start"]