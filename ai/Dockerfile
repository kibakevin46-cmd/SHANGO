# ╔════════════════════════════════════════════════════════════════════════╗
# ║                 DOCKERFILE - SHANGO AI API                             ║
# ║                                                                        ║
# ║  Containerise l'API FastAPI pour un déploiement facile                ║
# ╚════════════════════════════════════════════════════════════════════════╝

# Stage 1: Builder (construire l'image)
FROM python:3.10-slim as builder

WORKDIR /app

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# Copier requirements et installer pip packages
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt


# Stage 2: Runtime (image finale minimale)
FROM python:3.10-slim

WORKDIR /app

# Installer uniquement les packages runtime nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Copier les packages pip du builder
COPY --from=builder /usr/local/lib/python3.10/site-packages /usr/local/lib/python3.10/site-packages
COPY --from=builder /usr/local/bin /usr/local/bin

# Copier l'application
COPY shango_api.py .
COPY models/ ./models/

# Créer un utilisateur non-root pour la sécurité
RUN useradd -m -u 1000 appuser && chown -R appuser:appuser /app
USER appuser

# Port exposé
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD python -c "import requests; requests.get('http://localhost:8000/health')" || exit 1

# Lancer l'API
CMD ["uvicorn", "shango_api:app", "--host", "0.0.0.0", "--port", "8000"]
