import json
import os

log_file = "/home/sangam/.gemini/antigravity/brain/e74474c6-1c56-4cc7-9d32-ded42a474304/.system_generated/logs/overview.txt"
restored = 0
with open(log_file, "r") as f:
    for line in f:
        try:
            data = json.loads(line)
            if data.get("type") == "PLANNER_RESPONSE" and "tool_calls" in data:
                for call in data["tool_calls"]:
                    if call.get("name") == "write_to_file":
                        args = call.get("args", {})
                        target = args.get("TargetFile", "").strip("\"")
                        content = args.get("CodeContent", "")
                        if "app/Filament/" in target:
                            if content.startswith("\"") and content.endswith("\""):
                                content = json.loads(content)
                            
                            # Replace the type hint to fix the Filament issue!
                            content = content.replace("protected static ?string $navigationGroup", "protected static string|\UnitEnum|null $navigationGroup")
                            content = content.replace("protected static ?string $navigationIcon", "protected static string|\Illuminate\Contracts\Support\Htmlable|null $navigationIcon")
                            
                            print("Restoring:", target)
                            os.makedirs(os.path.dirname(target), exist_ok=True)
                            with open(target, "w") as out:
                                out.write(content)
                            restored += 1
        except Exception as e:
            pass
print("Restored:", restored)
