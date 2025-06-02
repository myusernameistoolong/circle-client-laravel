# Secure AI Integration Platform – Laravel Web App

This is a Laravel-based web application where users can watch different livestreams, a sort of Twitch but focussed on security aspects. It is designed to securely communicate with various external applications and AI services. While I developed this application independently, I also assisted in the architecture and integration planning of the other connected applications developed by team members.

> This project was developed as part of a school assignment for an **Security and Computer Networking** course.

---

## 🔐 Project Focus: Security

The core focus of this application is secure system communication. A full risk analysis was performed to anticipate potential issues with dependencies, protocols, and time-intensive components. Key security principles applied include:

- Single Responsibility Principle
- System independence and autonomy
- Risk mitigation through modular design and fallback planning
- Consultative support for complex components like digital signatures

---

## ⚙️ Stack & Technologies

- **Framework:** Laravel (PHP)
- **Protocols Used:**
  - HTTP (API communication)
  - RTMP (video streaming)
  - WebSocket (real-time chat & event handling)
- **Integrations:**
  - Streaming backend (via HTTP)
  - Video feed (via RTMP)
  - Chat server (via WebSocket)

---

## 🔁 Event Handling Overview

### Listens for:
- `message` → Receive incoming chat messages in a joined stream  
- `streamUsers` → Receive viewer count from the server  

### Emits:
- `joinstream` → User joins a stream  
- `chatMessage` → User sends a chat message  
- `disconnectUserFromStream` → User leaves the stream  

---

## 📊 Risk Analysis Summary

| Risk Description                          | Likelihood | Impact | Score | Mitigation                                                                 |
|-------------------------------------------|------------|--------|--------|-----------------------------------------------------------------------------|
| Stack changes                             | 3          | 2      | 6      | Plan architecture and confirm technical feasibility early                  |
| External system stack changes             | 3          | 0.5    | 1.5    | Maintain modularity and autonomy to reduce dependency risk                 |
| Complex components (e.g., signature logic)| 3          | 1      | 3      | Attempt solutions independently, escalate to peers when needed             |

---

## 🤝 Collaboration Context

Although this specific Laravel web app was created independently, it is part of a **larger ecosystem** of applications built by my peers. I played an advisory role in helping design the architecture and integration strategies of those external systems to ensure smooth interoperability and shared security goals.

---

## 📌 Educational Context

This project was part of the **Security and Computer Networking** course, aimed at:
- Designing secure software systems
- Managing inter-application communication
- Handling real-time and multimedia protocols securely
- Applying risk analysis and mitigation techniques

---

## 📄 License

This project is intended for educational use. Code and structure may be reused or adapted under the MIT License.
