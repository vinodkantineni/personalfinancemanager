# ▶️ Full BattleZone Atari Environment Runner using gymnasium
import gymnasium as gym
import time

def run_battlezone_episodes(num_episodes=1):
    # Create the Atari environment with real-time display
    env = gym.make("ALE/BattleZone-v5", render_mode="human")  # 'human' opens a window

    for episode in range(num_episodes):
        obs, info = env.reset()
        done = False
        total_reward = 0

        while not done:
            action = env.action_space.sample()  # Random action
            obs, reward, terminated, truncated, info = env.step(action)
            total_reward += reward
            done = terminated or truncated
            time.sleep(0.01)  # Small delay to smooth rendering

        print(f"✅ Episode {episode + 1} finished. Total reward: {total_reward}")

    env.close()

if __name__ == "__main__":
    run_battlezone_episodes(num_episodes=1)
