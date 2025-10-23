# Cursor Configuration

This directory contains Cursor IDE configuration for the Emarsys SDK project.

## Files

### `mcp.json` - Model Context Protocol Servers

Configures MCP servers that provide additional context to the AI assistant.

**Configured Servers:**

- **Context7 - Emarsys Core API Reference**
  - Provides instant access to [Emarsys API documentation](https://dev.emarsys.com/docs/core-api-reference/)
  - Library: `websites/dev_emarsys_core-api-reference`
  - [View on Context7](https://context7.com/websites/dev_emarsys_core-api-reference)

**Usage:**

When working with Cursor AI, you can reference the Emarsys API documentation by asking questions like:

- "According to the Emarsys API docs, what endpoints are available for contacts?"
- "Check the Emarsys API reference for rate limiting information"
- "What fields are required for creating a contact list according to the API?"

The AI will automatically fetch relevant documentation from Context7 to answer your questions accurately.

**Prerequisites:**

- Node.js/npm installed (for npx)
- Internet connection (to fetch documentation)
- Cursor IDE with MCP support enabled

**How It Works:**

1. Cursor reads `mcp.json` configuration
2. Launches Context7 MCP server via npx
3. Server provides indexed Emarsys API documentation
4. AI can query the docs in real-time while coding

### `rules/` - AI Development Rules

Contains markdown files with project-specific development guidelines:

- **`main-instructions.mdc`** - Complete development guide including:
  - Docker workflow
  - PHP best practices
  - Testing patterns
  - Code quality standards
  - Architecture guidelines

See [main-instructions.mdc](rules/main-instructions.mdc) for full documentation.

## Benefits

### 1. Accurate API Integration
- AI has access to official Emarsys API reference
- No guessing about endpoints, parameters, or responses
- Reduces errors from outdated or incorrect information

### 2. Faster Development
- Instant answers about API capabilities
- No manual documentation lookup
- AI suggests correct implementation patterns

### 3. Consistent Code Quality
- Development rules ensure consistent style
- AI follows project conventions automatically
- Reduces code review feedback

## Troubleshooting

### MCP Server Not Working

If the Context7 MCP server isn't connecting:

1. **Check Node.js installation:**
   ```bash
   node --version
   npm --version
   ```

2. **Test the MCP server manually:**
   ```bash
   npx -y @upstash/context7-mcp-server websites/dev_emarsys_core-api-reference
   ```

3. **Restart Cursor IDE:**
   - Close and reopen Cursor
   - MCP servers are initialized on startup

4. **Check Cursor logs:**
   - Look for MCP-related errors in Cursor's developer console
   - Help → Toggle Developer Tools

### Context7 Documentation Outdated

If the Emarsys API documentation seems outdated:

1. Check the [Context7 library page](https://context7.com/websites/dev_emarsys_core-api-reference)
2. See when it was last updated (shows "Update: X minutes ago")
3. Context7 automatically refreshes documentation periodically

## Resources

- **Context7 Documentation:** https://context7.com
- **Emarsys API Reference:** https://dev.emarsys.com/docs/core-api-reference/
- **MCP Protocol:** https://modelcontextprotocol.io/
- **Cursor MCP Guide:** https://docs.cursor.com/mcp

## Contributing

When adding new MCP servers:

1. Add configuration to `mcp.json`
2. Update this README with:
   - Server description
   - Usage examples
   - Prerequisites
3. Test the configuration works
4. Document any troubleshooting steps

