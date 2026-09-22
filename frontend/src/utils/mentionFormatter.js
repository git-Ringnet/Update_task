/**
 * Format comment / task text with mentions styling:
 * - @Name, @all, @Group -> Green (text-[#1A7A56] / text-emerald-700 font-bold)
 * - "Name (private mention) -> Orange (text-[#ea580c] font-bold)
 */

export const parseCommentTextClean = (content) => {
  if (!content) return ''
  return String(content)
    .replace(/^\[reply:\{.*?\}\]/, '')
    .replace(/!\[.*?\]\((.*?)\)/g, '')
    .replace(/📎\s*\[(.*?)\]\((.*?)\)/g, '')
    .replace(/<img[^>]*>/gi, '')
    .replace(/<a[^>]*>📎\s*Tệp đính kèm:[^<]*<\/a>/gi, '')
    .replace(/<span[^>]*>📎\s*Tệp đính kèm:[^<]*<\/span>/gi, '')
    .replace(/<[^>]+>/g, '')
    .replace(/<br\s*\/?>/gi, '\n')
    .trim()
}

export const formatCommentTextWithMentions = (content, usersList = [], groupsList = []) => {
  if (!content) return ''
  let text = parseCommentTextClean(content)

  // Escape basic HTML chars (except quotes which we need to detect)
  let escaped = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')

  // Normalize groups & users
  const rawGroups = Array.isArray(groupsList) ? groupsList : (groupsList?.value || [])
  const rawUsers = Array.isArray(usersList) ? usersList : (usersList?.value || [])

  const allGroups = [...rawGroups].sort((a, b) => (b.name?.length || 0) - (a.name?.length || 0))
  const allUsers = [...rawUsers].sort((a, b) => (b.name?.length || 0) - (a.name?.length || 0))

  // 1. Format private mentions: "Name (only for matched users)
  allUsers.forEach(u => {
    if (u && u.name) {
      const esc = u.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
      // Matches "Name or &quot;Name preceded by start of line, space, or quote
      const reg = new RegExp(`(?<=^|\\s)(?:"|&quot;)${esc}(?=\\s|$|[.,!?:;])`, 'gi')
      escaped = escaped.replace(reg, `<span class="text-[#ea580c] font-bold">"${u.name}</span>`)
    }
  })

  // 2. Format public mentions: @all and @Group
  escaped = escaped.replace(/(?<=^|\s)@all\b/gi, '<span class="text-[#1A7A56] font-bold">@all</span>')

  allGroups.forEach(g => {
    if (g && g.name) {
      const esc = g.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
      const reg = new RegExp(`(?<=^|\\s)@${esc}(?=\\s|$|[.,!?:;])`, 'gi')
      escaped = escaped.replace(reg, `<span class="text-[#1A7A56] font-bold">@${g.name}</span>`)
    }
  })

  // 3. Format public mentions: @User
  allUsers.forEach(u => {
    if (u && u.name) {
      const esc = u.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
      const reg = new RegExp(`(?<=^|\\s)@${esc}(?=\\s|$|[.,!?:;])`, 'gi')
      escaped = escaped.replace(reg, `<span class="text-[#1A7A56] font-bold">@${u.name}</span>`)
    }
  })

  // Generic fallback for any @word
  escaped = escaped.replace(/(?<=^|\s)@([^\s@,.:;!?()\n]+)(?![^<]*>|[^<>]*<\/span>)/g, '<span class="text-[#1A7A56] font-bold">@$1</span>')

  return escaped
}
