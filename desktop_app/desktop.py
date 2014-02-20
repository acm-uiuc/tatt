import wx

app = wx.App(redirect=True)
mainFrame = wx.Frame(None, title="Hello World", size=(300,200))
mainFrame.Show()
app.MainLoop()