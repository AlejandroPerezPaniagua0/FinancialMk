# FinancialMk front · multi-stage Vite build + Nginx static serve
#
# Stage 1 builds the SPA. Stage 2 serves the static bundle and proxies
# /api requests to the back service via the internal Docker network.

# ── Stage 1: build ────────────────────────────────────────────────────────
FROM node:20-alpine AS builder

ARG VITE_API_BASE_URL=/api
ENV VITE_API_BASE_URL=${VITE_API_BASE_URL}

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ── Stage 2: serve ────────────────────────────────────────────────────────
FROM nginx:1.27-alpine

# Static SPA bundle
COPY --from=builder /app/dist /usr/share/nginx/html

# Custom config: SPA fallback + proxy /api to back
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
